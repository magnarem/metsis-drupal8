<?php

namespace Drupal\metsis_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\SettingsCommand;

class MapSearchController extends ControllerBase {


    /* Callback from openlayers when boundingbox filter are drawed on map.
    Add current drawed boundingbox to solr search query */
    public function setBoundingBox() {
      $query_from_request = \Drupal::request()->query->all();
      $params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);

      $tllat = $params['tllat'];
      $tllon = $params['tllon'];
      $brlat = $params['brlat'];
      $brlon = $params['brlon'];
      $proj = $params['proj'];
      \Drupal::logger('metsis_search_map_search_controller')->debug("Got boundingbox with ENVELOPE(" .  $tllon . ',' . $brlon . ',' . $tllat . ',' . $brlat . ')');
      $bboxFilter = 'ENVELOPE(' . $tllon . ',' . $brlon . ',' . $tllat . ',' . $brlat . ')';

      //Get current session variables
      $session = \Drupal::request()->getSession();
      $session->set('bboxFilter', $bboxFilter);
      $session->set('tllat', $tllat);
      $session->set('tllon', $tllon);
      $session->set('brlat', $brlat);
      $session->set('brlon', $brlon);
      $session->set('proj', $proj);

        //Get saved configuration
        $config = \Drupal::config('metsis_search.settings');
        $map_location = $config->get('map_selected_location');
        $map_lat =  $config->get('map_locations')[$map_location]['lat'];
        $map_lon = $config->get('map_locations')[$map_location]['lon'];
        $map_zoom = $config->get('map_zoom');
        $map_additional_layers = $config->get('map_additional_layers_b');
        $map_projections = $config->get('map_projections');
        $map_init_proj =  $config->get('map_init_proj');
        $map_base_layer_wms_north =  $config->get('map_base_layer_wms_north');
        $map_base_layer_wms_south =  $config->get('map_base_layer_wms_south');
        $map_layers_list =  $config->get('map_layers');
        $map_filter = $config->get('map_bbox_filter');

      $data = [
        'metsis_search_map_block' => [
          'mapLat' => $map_lat, //to be replaced with configuration variables
          'mapLon' => $map_lon, //to be replaced with configuration variables
          'mapZoom' => $map_zoom, //to be replaced with configuration variables
          'init_proj' => $map_init_proj, //to be replaced with configuration variables
          'additional_layers' => $map_additional_layers, //to be replaced with configuration variables
          'tllat' => $tllat,
          'tllon' => $tllon,
          'brlon' => $brlon,
          'brlat' => $brlat,
          'proj' => $proj,
          'base_layer_wms_north' => $map_base_layer_wms_north,
          'base_layer_wms_south' => $map_base_layer_wms_south,
          'projections' => $map_projections,
          'layers_list' => $map_layers_list,
          'bboxFilter' => $bboxFilter,
          'mapFilter' => $map_filter,
        ],
      ];
      $response = new AjaxResponse();
      //$response->addCommand(new SettingsCommand(['metsis_search_map_block' => []], TRUE));
      $response->addCommand(new SettingsCommand ($data, TRUE));


      return $response;

 }

 /* select projection callback */
 public function setProjection() {
   $query_from_request = \Drupal::request()->query->all();
   $params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);

   $proj = $params['proj'];
   \Drupal::logger('metsis_search_map_search_controller')->debug("Got projection: " . $proj);
   //Get current session variables
   $session = \Drupal::request()->getSession();
   $session->set('proj', $proj);

     //Get saved configuration
     $config = \Drupal::config('metsis_search.settings');
     $map_location = $config->get('map_selected_location');
     $map_lat =  $config->get('map_locations')[$map_location]['lat'];
     $map_lon = $config->get('map_locations')[$map_location]['lon'];
     $map_zoom = $config->get('map_zoom');
     $map_additional_layers = $config->get('map_additional_layers_b');
     $map_projections = $config->get('map_projections');
     $map_init_proj =  $config->get('map_init_proj');
     $map_base_layer_wms_north =  $config->get('map_base_layer_wms_north');
     $map_base_layer_wms_south =  $config->get('map_base_layer_wms_south');
     $map_layers_list =  $config->get('map_layers');
     $map_filter = $config->get('map_bbox_filter');

   $data = [
     'metsis_search_map_block' => [
       'proj' => $proj,
     ],
   ];
   $response = new AjaxResponse();
   //$response->addCommand(new SettingsCommand(['metsis_search_map_block' => []], TRUE));
   $response->addCommand(new SettingsCommand ($data, TRUE));


   return $response;

}
 public function resetCallback() {
     \Drupal::logger('metsis_search')->debug("MapSearchController::resetCallback");
 }
}
