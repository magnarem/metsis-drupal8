<?php
/**
 * @file
 * Contains \Drupal\metsis_basket\Controller\MetsisBasketListingController.
 */

namespace Drupal\metsis_basket\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\metsis_lib\MetsisUtils;
use Drupal\metsis_basket\Entity\BasketItem;
use Drupal\Core\Entity\Controller\EntityListController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\MessageCommand;
/**
 * Default controller for the metsis_basket module.
 * {@inheritdoc}
 */
class MetsisBasketController extends ControllerBase  {

  public function myBasket() {
    //Get the user_id
    $user_id = (int) \Drupal::currentUser()->id();

    //Get the refering page
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');


    //Create content wrapper
    $build['content'] = [
      '#prefix' => '<div class="w3-container">',
      '#suffix' => '</div>'
    ];


    $build['content']['back'] = [
      '#markup' => '<a class="w3-btn" href="'. $referer . '">Go back to search </a>',
    ];
    $build['content']['dashboard'] = [
      '#markup' => '<a class="w3-btn" href="/metsis/bokeh/dashboard">Go to Dashboard</a>',
    ];

    $build['content']['view'] = views_embed_view('basket_view', 'embed_1');

    $build['#cache'] = [
      'contexts' => [
        'url.path',
        'url.query_args',
      ],
      'tags' => [
        'view',
      ],
    ];

    return $build;
  }

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
    $dar = MetsisUtils::msb_get_resources($metaid);
    foreach($dar['opendap'] as $res) {
      $fields = [
        'uid' => $user_id,
        'user_name' => $user_name,
        'title' => $title,
        'session_id' => session_id(),
        'basket_timestamp' => time(),
        'metadata_identifier' => $metaid,
  //      'data_access_resource_http' => $dar['http'],
  //      'data_access_resource_odata' => $dar['odata'],
        'data_access_resource_opendap' => $res,
  //      'data_access_resource_ogc_wms' => $dar['ogc_wms'],
      ];
      //dpm($res);
      $query = \Drupal::database()->insert('metsis_basket')->fields($fields)->execute();
    }
    //$objects = \Drupal::entityTypeManager()->getStorage('metsis_basket', array($iid));
    //\Drupal::messenger()->addMessage("Dataset added to basket:  " . $metaid);
    //\Drupal::cache()->invalidate('metsis_basket_block');//Check if we already have an active bboxFilter


    $basket_count = $this->get_user_item_count($user_id);
    $selector = '#myBasketCount';
    //$markup = '<a href="/metsis/elements?metadata_identifier="'. $id .'"/>Child data..['. $found .']</a>';

    $markup = '<span id="myBasketCount" class="w3-badge w3-green">' . $basket_count . '</span>';

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#addtobasket-' . $metaid ,'Add to Basket &#10004;'));
    $response->addCommand(new HtmlCommand($selector,$markup));
    $response->addCommand(new MessageCommand("Dataset added to basket:  " . $metaid));

    return $response;
  }

  public static  function get_user_item_count($user_id) {
    $query = \Drupal::database()->select('metsis_basket', 'm');
    $query->fields('m', array('iid'));
    $query->condition('m.uid', $user_id, '=');
    $results = $query->execute()->fetchAll();
    return count($results);
  }



}
