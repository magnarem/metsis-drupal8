<?php
/*
 * @file
 * Contains \Drupal\metsis_search\Plugin\Block\GcmdBlock
 *
 * BLock to show search map
 *
 */
namespace Drupal\metsis_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a Block.
 *
 * @Block(
 *   id = "metsis_gcmd_block",
 *   admin_label = @Translation("GCMD Keywords Block"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class GcmdBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   * Add js to block and return renderarray
   */
  public function build() {
    \Drupal::logger('metsis_search')->debug("Building Gcmd block");

    //Check if we already have an active bboxFilter
      $session = \Drupal::request()->getSession();
      $list = $session->get('gcmd');
    return [
      '#markup' => $list,
      '#cache' => [
      'contexts' => [
        'url.path',
        'url.query_args',
      ],
      ],

    ];

  }
  public function getCacheMaxAge() {
  return 0;
}
}
