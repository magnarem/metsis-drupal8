<?php

namespace Drupal\metsis_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;

class MapSearchController extends ControllerBase {

    public function ajaxCallback() {
      $query_from_request = \Drupal::request()->query->all();
      $params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);

      $tllat = $params['tllat'];
      $tllon = $params['tllon'];
      $brlat = $params['brlat'];
      $brlon = $params['brlon'];
      \Drupal::logger('metsis_search')->debug("Got boundingbox with ENVELOPE(" . $tllat . ',' . $tllon . ',' . $brlat . ',' . $brlon . ')');
      $bboxFilter = 'ENVELOPE(' . $tllon . ',' . $brlon . ',' . $tllat . ',' . $brlat . ')';
      $tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
      $tempstore->set('bboxFilter', $bboxFilter);
      $tempstore->set('tllat', $tllat);
      $tempstore->set('tllon', $tllon);
      $tempstore->set('brlat', $brlat);
      $tempstore->set('brlon', $brlon);


      return new \Drupal\Core\Ajax\AjaxResponse("{ success: true }");

 }
 public function resetCallback() {
     \Drupal::logger('metsis_search')->debug("MapSearchController::resetCallback");
 }
}
