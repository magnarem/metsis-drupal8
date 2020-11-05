<?php
/*
 * @file
 * Contains \Drupal\metsis_dashboard_bokeh\Plugin\Block\BasketBlock
 *
 * BLock to show basket button and number of items
 *
 */
namespace Drupal\metsis_dashboard_bokeh\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a Block.
 *
 * @Block(
 *   id = "metsis_basket_block",
 *   admin_label = @Translation("METSISBasket Block"),
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
    \Drupal::logger('metsis_dashboard_bokeht')->debug("Building Basket Block");

    //Check if we already have an active bboxFilter
    $basket_count = $this->getUserItemCount();
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

  function getUserItemCount() {

    /**
     * Get count of resources from private tempstore
     */


    return $count;
  }
}
