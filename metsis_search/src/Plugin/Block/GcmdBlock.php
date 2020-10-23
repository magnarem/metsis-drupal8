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
      //$list = $session->get('gcmd');
      $build['wrapper'] = [
        '#prefix' => '<div id="gcmdblock">',
        '#suffix' => '</div>'
      ];

      $build['wrapper']['gcmd_l1'] = \Drupal::service('plugin.manager.block')
        ->createInstance('facet_block:gcmd_keywords')
        ->build();

        $build['wrapper']['gcmd_l1']['#prefix'] ='<div id="gcmd_l1">';
        $build['wrapper']['gcmd_l1']['#suffix'] ='</div>';

        $build['wrapper']['delimeter1'] = [
          '#prefix' => '<div "class="delimeter">',
          '#markup' => '➤',
          '#suffix' => '</div>'
        ];
        $build['wrapper']['gcmd_l2'] = \Drupal::service('plugin.manager.block')
          ->createInstance('facet_block:keywords_level2')
          ->build();
          $build['wrapper']['gcmd_l2']['#prefix'] ='<div id="gcmd_l2">';
          $build['wrapper']['gcmd_l2']['#suffix'] ='</div>';

          $build['wrapper']['delimeter2'] = [
            '#prefix' => '<div "class="delimeter">',
            '#markup' => '➤',
            '#suffix' => '</div>'
          ];
          $build['wrapper']['gcmd_l3'] = \Drupal::service('plugin.manager.block')
            ->createInstance('facet_block:keywords_level3')
            ->build();
        $build['#cache'] = [
        //'max-age' => 0,
        //'tags' =>$this->getCacheTags(),
          'contexts' => [
            //  'route',

            'url.path',
            'url.query_args',
          ],
        ];

        $build['#attached'] = [
          'library' => [
            'metsis_search/gcmd',
          ],
        ];

        return $build;

  }
  public function getCacheMaxAge() {
  return 1;
}
}
