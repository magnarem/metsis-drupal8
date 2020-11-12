<?php

namespace Drupal\metsis_dashboard_bokeh\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Render\Markup;



class DashboardBokehController extends ControllerBase {

    public function build() {
      $config = \Drupal::config('metsis_dashboard_bokeh.configuration');
      $backend_uri = $config->get('dashboard_bokeh_service');
      //$backend_uri = 'https://pybasket.epinux.com/dashboard';
      //$backend_uri = 'https://pybasket.epinux.com/dashboard-bkapp-api';
      //$backend_uri = 'http://178.63.52.22:7000/dashboard-bkapp-api?datasources=a&datasources=b&datasources=c&email=me%40you.web';
      //var_dump($backend_uri);
      $resources_test = 'http://hyrax.epinux.com/opendap/SN99938.nc,http://hyrax.epinux.com/opendap/ctdiaoos_gi2007_2009.nc,http://hyrax.epinux.com/opendap/itp01_itp1grd2042.nc';

      \Drupal::logger('metsis_dashboard_bokeh')->debug(t("@backend", ['@backend' => $backend_uri ] ) );
      $tempstore = \Drupal::service('tempstore.private');
      // Get the store collection.
      $store = $tempstore->get('metsis_dashboard_bokeh');
      $resources = $store->get('basket');
      //dpm($resources);

      /**
       * FIXME: This IF-caluse is for testing only. Should be removed for prod
       */
      if($resources == NULL) { $resources = explode(',', $resources_test); }

      $markup = $this->getDashboard($backend_uri, $resources);
      \Drupal::logger('metsis_dashboard_bokeh')->debug(t("@markup", ['@markup' => $markup ] ) );

      // Build page
      $build['dashboard-wrapper'] = [
        '#type' => 'markup',
        '#markup' => '<div id="bokeh-dashboard" class="w3-container">',
        '#attached' => [
          'library' => [
            'metsis_dashboard_bokeh/dashboard',
          ],
        ],
      ];
      $build['dashboard-wrapper']['content'] = [
        '#type' => 'markup',
        '#markup' => $markup,
        '#suffix' => '</div>',
        '#allowed_tags' => ['script'],

      ];

      return $build;
    }

    function getDashboard($backend_uri, $resources) {

      //Create datasources query parameters from resources
      $res_list = $resources;
      $query_params = "?";
      foreach ($res_list as $r) {
        $query_params .= 'datasources=' . urlencode($r) . '&';
      }
      // Add user email query parameter
      $query_params .= 'email=' .  \Drupal::currentUser()->getEmail();
  /*    try {
           $client = \Drupal::httpClient();
           //$client->setOptions(['debug' => TRUE]);
           $request = $client->request('GET', $backend_uri,
           ['debug' => TRUE,
             'headers' => [
             'Accept' => 'application/json',
             ],
             'query' => [
               'datasources' => $resources,
               'email' =>  \Drupal::currentUser()->getEmail(),
             ],
           ]
       );
       $responseStatus = $request->getStatusCode();
       $data = $request->getBody();
       $json_response = \Drupal\Component\Serialization\Json::decode($data);
     }*/

      try {
          $client = \Drupal::httpClient();
          //$client->setOptions(['debug' => TRUE]);
          $request = $client->request('GET', $backend_uri . $query_params,
          ['debug' => TRUE,
            'headers' => [
            'Accept' => 'application/json',
            ],
          ]
      );

        $responseStatus = $request->getStatusCode();
        $data = $request->getBody();
        $json_response = \Drupal\Component\Serialization\Json::decode($data);
        //return ($json_response);
      }
      catch (Exception $e){
        \Drupal::messenger()->addError("Could not contact bokeh dashboard api at @uri .", [ '@uri' => $backend_uri]);
        \Drupal::messenger()->addError($e);
      }

        return $json_response;
    }
  }
