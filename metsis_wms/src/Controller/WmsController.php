<?php /**
 * @file
 * Contains \Drupal\metsis_wms\Controller\WmsController.
 */

namespace Drupal\metsis_wms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;
use Solarium\QueryType\Select\Result\Document;
use Solarium\Core\Query\DocumentInterface;
use Solarium\Core\Query\Result\ResultInterface;
use Drupal\Core\Render\Markup;


use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
//use Drupal\metsis_wms\WmsUtils;
/**
 * Default controller for the metsis_qsearch module.
 */
class WmsController extends ControllerBase {

  public function getWmsMap() {
    $query_from_request = \Drupal::request()->query->all();
    $query = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');
    //var_dump($query);
    //var_dump($referer);

    /** Variables to be read from configuratuon
    * TODO: The followin variables need to come from module config
    */
    $wms_which_base_layer = 'north';
    $wms_overlay_border = 'true';
    $wms_product_select = 'false';
    $wms_lat =  78.22314167;
    $wms_lon = 15.64685556;
    $wms_zoom = 3.5;

    $markup = 'No Data Found!';
    $webMapServers = [];
    \Drupal::logger('metsis_wms')->debug("Got query parameters: " . count($query));
    if (count($query) > 0) {
      $datasets = explode(",", $query['dataset']);
      $webMapServers = $this->getWebMapServers($datasets);
      $markup = $this->prepareWmsMarkup($wms_lon,
                                    $wms_lat,
                                    $wms_zoom,
                                    $wms_which_base_layer,
                                    $wms_overlay_border,
                                    $webMapServers,
                                    $wms_product_select);
      }

    //Return $page as renderarray
    return [
      '#type' => '#markup',
      '#markup' => $markup,
      '#attached' => [
        'library' => [
          'metsis_wms/replace.css',
          #'metsis_wms/replace.jquery_min',
          #'metsis_wms/replace.jquery_core',
          #'metsis_wms/replace.jquery_bbq',
          #'metsis_wms/replace.misc_overlay',
          #'metsis_wms/replace.jquery_cookie',
          #'metsis_lib/utils',
          'metsis_wms/jquery',
          'metsis_wms/jquery.ui',
          'metsis_wms/jquery.cycle',
          'metsis_wms/dropdown',
          'metsis_wms/bundle',
          'metsis_wms/wmsmap',
          #'metsis_lib/adc_buttons'
        ],
        'drupalSettings' => [
          'metsis_wms' => [
            'mapLat' => $wms_lat, //to be replaced with configuration variables
            'mapLon' => $wms_lon, //to be replaced with configuration variables
            'mapZoom' => $wms_zoom, //to be replaced with configuration variables
            'whichBaseLayer' => $wms_which_base_layer,
            'overlayBorder' => $wms_overlay_border,
            'productSelect' => $wms_product_select,
            'webMapServers' => $webMapServers,
            'init_proj' => 'EPSG:4326', //to be replaced with configuration variables
            'additional_layers' => FALSE, //to be replaced with configuration variables

            ],
          ],
      ],
    '#allowed_tags' => ['div','script', 'a'],
    ];
  }

  public function getWebMapServers($datasets) {

    global $base_url;


    $fields = [
      "id",
      "data_access_url_ogc_wms",
      "data_access_wms_layers",
    ];
    $wms_url_lhs = $base_url . "/" . "metsis/map/getcap?dataset=";  //TODO: Read this from routing config
    $wms_data = [];
    $layers = [];
    $web_map_servers = [];
    $wms_restrict_layers = 1;  //TODO: Read this from WMS Config

    $capdoc_postfix = "?SERVICE=WMS&REQUEST=GetCapabilities"; //TODO: Read this from config

    \Drupal::logger('metsis_wms')->debug("Calling getFields");
    $resultset = $this->getFields($datasets,$fields);
    //$documents = $result->getDocuments());

    foreach($resultset as $document) {
      $fields = $document->getFields();
      $mi = $fields['id'][0];
      foreach($fields['data_access_url_ogc_wms'] as $wms_url) {
          $wms_data[$mi]['dar'][] = $wms_url;
      }

      if(isset($fields['data_access_wms_layers'])) {
        foreach($fields['data_access_wms_layers'] as $wms_layer) {
          $wms_data[$mi]['layers'][] = $wms_layer;
        }
        $layers = implode('","', $wms_data[$mi]['layers'][]);
        //$layers = '"' . $layers . '"';
      }
      if ($wms_restrict_layers === 1 && isset($wms_data[$mi]['layers'])) {

        $web_map_servers[$mi] = '{capabilitiesUrl: "' . $wms_url_lhs . $wms_data[$mi]['dar'][0] . $capdoc_postfix . '",activeLayer:"' . $wms_data[$mi]['layers'][0] . '",layers: [' . $layers . ']}';
      }
      else {
        $web_map_servers[$mi] = '{capabilitiesUrl: "' . $wms_url_lhs . $wms_data[$mi]['dar'][0] . $capdoc_postfix . '",activeLayer:"",layers: []}';
      }
    }
    $webMapServers = implode(',', $web_map_servers);

    //$wms_data[$mi]['dar'] = msb_concat_data_access_resource($ldar['response']['docs'][0][$fields[0]]);
  /*  if(isset( $ldar['response']['docs'][0][$fields[1]])) {
      $wms_data[$mi]['layers'] = $ldar['response']['docs'][0][$fields[1]];
      //var_dump($wms_data);
      $layers = implode('","', $wms_data[$mi]['layers'] = $ldar['response']['docs'][0][$fields[1]]);
      //var_dump($layers);
      $layers = '"' . $layers . '"';
    }
    else {
      $layers = [];
    }
    if(isset($wms_data[$mi]['dar']['OGC_WMS'])) {
    if ($metsis_conf['wms_restrict_layers'] === 1) {
      $web_map_servers[$mi] = '{capabilitiesUrl: "' . $wms_url_lhs . $wms_data[$mi]['dar']['OGC_WMS']['url'] . CAPDOC_POSTFIX . '",activeLayer:"' . $wms_data[$mi]['layers'][0] . '",layers: [' . $layers . ']}';
    }
    else {
      $web_map_servers[$mi] = '{capabilitiesUrl: "' . $wms_url_lhs . $wms_data[$mi]['dar']['OGC_WMS']['url'] . CAPDOC_POSTFIX . '",activeLayer:"",layers: []}';
    }
  }
  else { return Markup::create('Cannot visualise item. Missing OGC WMS resource </br> <a class="adc-button adc-sbutton adc-back" href="' . $referer . '">Back to results</a>');}
  }
  $webMapServers = implode(',', $web_map_servers);
  if (is_array($metadata_identifier)) {
    $wms_urls = [];
    foreach ($metadata_identifier as $eu) {
      $wms_urls[] = $eu . CAPDOC_POSTFIX;
    }
    $wms_url_rhs = implode(",", $wms_urls);
  }
  else {
    $wms_url_rhs = $metadata_identifier . CAPDOC_POSTFIX;
  }*/
return $webMapServers;


  }

  public function prepareWmsMarkup($wms_map_center_lon,
                                $wms_map_center_lat,
                                $wms_map_init_zoom,
                                $wms_which_base_layer,
                                $wms_overlay_border,
                                $webMapServers,
                                $wms_product_select
                            ) {
    //Get the referer uri
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');

    $string = <<<EOM
      <div class="ajax">
        <div class="map container ajax">
            <div id="map"></div>
            <div id="map-menu" class="layer-switcher"></div>
            <div id="lyr-switcher"></div>
            <div id="proj-container"></div>
            <div id="timeslider-container"></div>
        </div>
        <script type="text/javascript">
<!--      (function ($,Drupal) { -->
        var sClient;
        $(document).ready(function () {
            var wms = mapClient
                    .wms({
                        lon: $wms_map_center_lon,
                        lat: $wms_map_center_lat,
                        zoom: $wms_map_init_zoom,
                        whichBaseLayer: '$wms_which_base_layer',
                        overlayBorder: $wms_overlay_border,
                        webMapServers: [
                          $webMapServers
                        ],
                       productSelect: $wms_product_select});
        });
  <!--  }); -->

    </script>
    <script type="text/javascript">
     function reloadPage() {
       location.reload();
     }
    </script>

        <div class="center">
            <div class="botton-wrap">
              <br><br>
               <a class="button button--small adc-back" href="$referer">Back to results</a>
               <a class="button button--small" href="/basket">Basket</a>
               <a class="button button--small" onclick="reloadPage()">Reset</a>
            </div>
        </div>
      </div>

<script type="text/javascript">

  </script>
EOM;

/*
<!--
        $(document).ready(function () {
            var wms = mapClient
                    .wms({
                        lon: $wms_map_center_lon,
                        lat: $wms_map_center_lat,
                        zoom: $wms_map_init_zoom,
                        whichBaseLayer: '$wms_which_base_layer',
                        overlayBorder: $wms_overlay_border,
                        webMapServers: [
                          $webMapServers
                        ],
                       productSelect: $wms_product_select});
        });
    }); -->

*/
  return Markup::create($string);
  }

  public function getFields($metadata_identifier, $fields) {
    /** @var Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('drupal8');

    /** @var SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();

    foreach($metadata_identifier as $id) {
      \Drupal::logger('metsis_wms')->debug("setQuery: metadata_identifier: " .$id);
      $solarium_query->setQuery('metadata_identifier:'.$id);
    }
    //$solarium_query->addSort('sequence_id', Query::SORT_ASC);
    $solarium_query->setRows(2);
    $solarium_query->setFields($fields);

    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    \Drupal::logger('metsis_wms')->debug("found :" .$found);
    // The total number of documents returned from the query.
    //$count = $result->count();

    // Check the Solr response status (not the HTTP status).
    // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
    //$status = $result->getStatus();

    // An array of documents. Can also iterate directly on $result.
    return $result;


  }

}
