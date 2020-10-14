<?php
/*
 * @file
 * Contains \Drupal\metsis_basket\Plugin\Block\BasketBlock
 *
 * BLock to show basket button and number of items
 *
 */
namespace Drupal\metsis_basket\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a Block.
 *
 * @Block(
 *   id = "metsis_basket_block",
 *   admin_label = @Translation("METSIS Basket Block"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class BasketBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   * Add js to block and return renderarray
   */
  public function build() {
    \Drupal::logger('metsis_basket')->debug("Building Basket Block");

    //Check if we already have an active bboxFilter
    $user_id = (int) \Drupal::currentUser()->id();
    $basket_count = get_user_item_count($user_id);
    //Return render array
    return [
       '#markup' => $this->t('<div class=basket-block><a id="myBasket" class="adc-button adc-sbutton basket-link" href="/metsis/basket">My Basket (' . $basket_count .')</a></div>'),
        '#allowed_tags' => ['a', 'div'],
        '#cache' => [
          'contexts' => [
            'url.path',
            'url.query_args',
          ],
        ],
        '#attached' => [
          'library' => [
            'metsis_lib/adc_button'
          ],

        ],

    ];

  }
}
