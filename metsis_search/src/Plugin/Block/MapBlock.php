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

    //Check if we already have an active bboxFilter
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

    //Get saved configuration
    $config = \Drupal::config('metsis_search.settings');
    $map_location = $config->get('map_selected_location');
    $map_lat =  $config->get('map_locations')[$map_location]['lat'];
    $map_lon = $config->get('map_locations')[$map_location]['lon'];
    $map_zoom = $config->get('map_zoom');
    $map_additional_layers = $config->get('map_additional_layers');
    $map_init_proj = $config->get('map_init_proj');
    $map_base_layer_wms_north =  $config->get('map_base_layer_wms_north');
    $map_base_layer_wms_south =  $config->get('map_base_layer_wms_south');
    $map_search_text =  $config->get('map_search_text');

    $map_projections = $config->get('map_projections');
    $map_init_proj =  $config->get('map_init_proj');
    $map_search_text =  $config->get('map_search_text');
    $map_layers_list =  $config->get('map_layers');


 //Return render array
    return [
       '#markup' => '',
        //'#theme' => 'block__mapblockformetsissearch',
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
              'mapLat' => $map_lat, //to be replaced with configuration variables
              'mapLon' => $map_lon, //to be replaced with configuration variables
              'mapZoom' => $map_zoom, //to be replaced with configuration variables
              'init_proj' => $map_init_proj, //to be replaced with configuration variables
              'additional_layers' => $map_additional_layers,
              'base_layer_wms_north' => $map_base_layer_wms_north,
              'base_layer_wms_south' => $map_base_layer_wms_south,
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
