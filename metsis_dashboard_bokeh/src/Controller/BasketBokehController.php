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
use Drupal\Core\Ajax\MessageCommand;
/**
 * Default controller for the metsis_basket module.
 * {@inheritdoc}
 */
class BasketBokehController extends ControllerBase  {

  /**
   * Add opendap resources to private tempstore baslet
   */
  public function add($opendap_uri) {
    \Drupal::logger('metsis_basket')->debug("Calling add to basket function");
    $user_id = (int) \Drupal::currentUser()->id();
    $user_name = \Drupal::currentUser()->getAccountName();

    $basket_count = get_user_item_count($user_id);
    $selector = '#myBasket';
  
    $markup = 'My Basket ('. $basket_count .')';

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#addtobasket-' . $metaid ,'Add to Basket &#10004;'));
    $response->addCommand(new HtmlCommand($selector,$markup));
    $response->addCommand(new MessageCommand("Dataset added to basket:  " . $metaid));

    return $response;
  }

  function getUserItemCount() {

    /**
     * Get count of resources from private tempstore
     */


    return $count;
  }
}
