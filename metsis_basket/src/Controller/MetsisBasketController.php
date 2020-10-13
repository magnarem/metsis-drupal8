<?php
/**
 * @file
 * Contains \Drupal\metsis_basket\Controller\MetsisBasketListingController.
 */

namespace Drupal\metsis_basket\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\metsis_lib\MetsisUtils;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\metsis_basket\Entity\MetsisBasket;
use Drupal\Core\Entity\Controller\EntityListController;
/**
 * Default controller for the metsis_basket module.
 * {@inheritdoc}
 */
class MetsisBasketController extends ControllerBase  {

  public function listing($iid) {
    \Drupal::logger('metsis_basket')->debug("Listing item with iid: " . $iid);
    //$objects = \Drupal::entityTypeManager()->getStorage('metsis_basket', array($iid));


    //$objects = MetsisBasket::load($iid);
    //$mb = $objects[$iid];
    // @FIXME
    // drupal_set_title() has been removed. There are now a few ways to set the title
    // dynamically, depending on the situation.
    //
    //
    // @see https://www.drupal.org/node/2067859
    // drupal_set_title($mb->name);
    $view_builder = \Drupal::entityTypeManager()
      ->getViewBuilder('metsis_basket_item');
    $entity = \Drupal::entityTypeManager()
      ->getStorage('metsis_basket_item')->load($iid);
      \Drupal::logger('metsis_basket')->debug("Loaded entity with iid: " . $entity->id());
    return $view_builder
      ->view($entity, 'full');


  }

  public function add($metaid) {
    \Drupal::logger('metsis_basket')->debug("Calling add to basket function");
    $user_id = (int) \Drupal::currentUser()->id();
    $user_name = \Drupal::currentUser()->getAccountName();
    $title = MetsisUtils::msb_get_title($metaid);
      $fields = [
        'uid' => $user_id,
        'user_name' => $user_name,
        'session_id' => session_id(),
        'basket_timestamp' => time(),
        'metadata_identifier' => $metaid,
      ];
      $query = \Drupal::database()->insert('metsis_basket')->fields($fields)->execute();
    //$objects = \Drupal::entityTypeManager()->getStorage('metsis_basket', array($iid));
    \Drupal::messenger()->addMessage("Dataset added to basket:  " . $metaid);
    $response = new AjaxResponse();
    return $response;
  }
}
