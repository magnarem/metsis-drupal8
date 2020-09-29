<?php
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;

class WmsUtils {
  public function getWebMapServers($datasets) {

    global $base_url;


    $fields = [
      "data_access_resource_ogc_wms",
      "data_access_wms_layers",
    ];
    $wms_url_lhs = $base_url . "/" . "metsis/map/getcap?dataset=";  //TODO: Read this from routing config
    $wms_data = [];
    $web_map_servers = [];
    $wms_restrict_layers = 1;  //TODO: Read this from WMS Config

    $capdoc_postfix = "?SERVICE=WMS&REQUEST=GetCapabilities"; //TODO: Read this from config

    \Drupal::logger('metsis_wms')->debug("Calling getFields");
    $documents = this::getFields($datasets,$fields);


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
return $web_map_servers;


  }

  public function prepareWmsMarkup() {
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
        <div class="center">
            <div class="botton-wrap">
               <a class="button button--small adc-back" href="$referer">Back to results</a>
               <a class="button button--small" href="/basket">Basket</a>
               <a class="button button--small" onclick="reloadPage()">Reset</a>
            </div>
        </div>
      </div>
EOM;
  return Markup::create($string);
  }

  public function getFields($metadata_identifier, $fields) {
    /** @var Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('drupal8');

    /** @var SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();
    $solarium_query->setQuery('metadata_identifier:'.$metadata_identifier);
    //$solarium_query->addSort('sequence_id', Query::SORT_ASC);
    //$solarium_query->setRows(1);
    $solarium_query->setFields($fields);

    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    \Drupal::logger('metsis_wms')->debug("Got " . $metadata_identifier . ', found :' .$found);
    // The total number of documents returned from the query.
    //$count = $result->count();

    // Check the Solr response status (not the HTTP status).
    // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
    //$status = $result->getStatus();

    // An array of documents. Can also iterate directly on $result.
    return $result->getDocuments();


  }
}
 ?>
