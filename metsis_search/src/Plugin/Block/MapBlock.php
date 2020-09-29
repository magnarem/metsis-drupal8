<?php
/*
 * @file
 * Contains \Drupal\metsis_search\Plugin\Block\MapBlock
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
 *   id = "metsis_search_map_block",
 *   admin_label = @Translation("Map Block for METSIS Search"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class MapBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   * Add js to block and return renderarray
   */
  public function build() {
    \Drupal::logger('metsis_search')->debug("Building MapSearchForm");
    $tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
    $bboxFilter = $tempstore->get('bboxFilter');
    $tllat = "";
    $tllon = "";
    $brlat = "";
    $brlon = "";
    if ($bboxFilter != NULL) {
      $tllat = $tempstore->get('tllat');
      $tllon = $tempstore->get('tllon');
      $brlat = $tempstore->get('brlat');
      $brlon = $tempstore->get('brlon');
      \Drupal::logger('metsis_search')->debug("Got input filter vars: " .$tllat .','. $tllon .','.$brlat.','.$brlon);


    }

    return [
        '#markup' => '',
        '#tllat' => $tllat,
        '#tllon' => $tllon,
        '#brlat' => $brlat,
        '#brlon' => $brlon,
        '#cache' => [
          'contexts' => [
            'url.path',
            'url.query_args',
          ],
        ],
        '#attached' => [
          'library' => [
            'metsis_search/search_map'
          ],
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
          ],
        ],
        '#attributes' => [
              'id' => 'map-search',
      ],
    ];

  }
}
