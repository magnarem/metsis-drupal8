<?php
/**
 * @file
 *
 */

namespace Drupal\metsis_dashboard_bokeh\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\MessageCommand;
/**
 * Default controller for the metsis_basket module.
 * {@inheritdoc}
 */
class BasketBokehController extends ControllerBase  {

  /**
   * Add opendap resources to private tempstore baslet
   */
  public function add($metaid) {
      \Drupal::logger('metsis_basket_controller')->debug("/metsis/basket/add");
    $query_from_request = \Drupal::request()->query->all();
    $query = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);
    if(isset($query['datasource'])) {
      $opendap_uri = $query['datasource'];
      \Drupal::logger('metsis_basket_controller')->debug("Calling add to basket function with od_uri: @od", ['@od' => $opendap_uri]);
      $user_id = (int) \Drupal::currentUser()->id();
      $user_name = \Drupal::currentUser()->getAccountName();

      $opendap_uri = urldecode($opendap_uri);

      $selector = '#myBasketCount';

      $tempstore = \Drupal::service('tempstore.private');
      // Get the store collection.
      $store = $tempstore->get('metsis_dashboard_bokeh');
      $datasources = [];
      $datasources = $store->get('basket');
      if($datasources != NULL) {
        $basket_count = array_unshift($datasources, $opendap_uri);
      }
      else {
        $datasources = [];
        $basket_count = array_unshift($datasources, $opendap_uri);
      }
      $store->set('basket', $datasources);
      //$basket_count = $this->get_user_item_count($store);

      $markup = '<span id="myBasketCount" class="w3-badge w3-green">' . $basket_count . '</span>';

      $response = new AjaxResponse();
      $response->addCommand(new HtmlCommand('#addtobasket-' . $metaid ,'Add to Basket &#10004;'));
      $response->addCommand(new ReplaceCommand($selector,$markup));
      $response->addCommand(new MessageCommand("Dataset added to basket:  " . $metaid));

      return $response;
    }
    else {
      $response = new AjaxResponse();
      $response->addCommand(new MessageCommand("Something went wrong", 'warn'));
      return $response;
    }
  }

  function getUserItemCount($store) {

    /**
     * Get count of resources from private tempstore
     */

    $count = 1;
    return $count;
  }
}
