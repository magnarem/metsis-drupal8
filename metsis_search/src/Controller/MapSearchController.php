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

    public function ajaxCallback() {
      $query_from_request = \Drupal::request()->query->all();
      $params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);

      $tllat = $params['tllat'];
      $tllon = $params['tllon'];
      $brlat = $params['brlat'];
      $brlon = $params['brlon'];
      \Drupal::logger('metsis_search_map_search_controller')->debug("Got boundingbox with ENVELOPE(" .  $tllon . ',' . $brlon . ',' . $tllat . ',' . $brlat . ')');
      $bboxFilter = 'ENVELOPE(' . $tllon . ',' . $brlon . ',' . $tllat . ',' . $brlat . ')';
      $tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
      $tempstore->set('bboxFilter', $bboxFilter);
      $tempstore->set('tllat', $tllat);
      $tempstore->set('tllon', $tllon);
      $tempstore->set('brlat', $brlat);
      $tempstore->set('brlon', $brlon);

      $data = [
        'drupalSettings' => [
        'metsis_search' => [
          'mapLat' => 78.22314167, //to be replaced with configuration variables
          'mapLon' => 15.64685556, //to be replaced with configuration variables
          'mapZoom' => 3.5, //to be replaced with configuration variables
          'init_proj' => 'EPSG:4326', //to be replaced with configuration variables
          'additional_layers' => FALSE, //to be replaced with configuration variables
          'tllat' => $tllat,
          'tllon' => $tllon,
          'brlon' => $brlon,
          'brlat' => $brlat,
        ],
      ]];
      $response = new AjaxResponse();
      $response->addCommand(new SettingsCommand ($data, TRUE));


      return $response;

 }
 public function resetCallback() {
     \Drupal::logger('metsis_search')->debug("MapSearchController::resetCallback");
 }
}
