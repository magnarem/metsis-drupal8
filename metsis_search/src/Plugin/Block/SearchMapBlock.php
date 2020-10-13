<?php
/*
 * @file
 * Contains \Drupal\metsis_search\Plugin\Block\SearchMapBlock
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
 *   id = "metsis_search_map",
 *   admin_label = @Translation("METSIS Search Map"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class SearchMapBlock extends BlockBase implements BlockPluginInterface
{

  /**
   * {@inheritdoc}
   * Add js to block and return renderarray
   */
    public function build()
    {
        // Get the module path
        $module_handler = \Drupal::service('module_handler');
        $module_path = $module_handler->getModule('metsis_search')->getPath();
        // Get the bounding box drawn on the map
        \Drupal::logger('metsis_search')->debug("Building MapSearchForm");
        $tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
        $bboxFilter = $tempstore->get('bboxFilter');
        $tllat = "";
        $tllon = "";
        $brlat = "";
        $brlon = "";
        if ($bboxFilter != null) {
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
        $map_additional_layers = $config->get('map_additional_layers_b');
        $map_projections = $config->get('map_projections');
        $map_init_proj =  $config->get('map_init_proj');
        $map_search_text =  $config->get('map_search_text');
        $map_base_layer_wms_north =  $config->get('map_base_layer_wms_north');
        $map_base_layer_wms_south =  $config->get('map_base_layer_wms_south');
        $map_search_text =  $config->get('map_search_text');
        $map_layers_list =  $config->get('map_layers');
        $map_pins = $config->get('map_pins_b');
        $map_filter = $config->get('map_bbox_filter');

        //Get the extracted info from tempstore
        $tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
        $extracted_info = $tempstore->get('extracted_info');

        /**
         * Create the render array
         */

         $build['map-res-popup'] = [
           '#markup' => '<div id="popup-map-res" class="ol-popup"><div id="popup-map-res-content"></div></div>',
         ];
        // search-map wrapper
        $build['search-map'] = [
     '#prefix' => '<div id="map-res" class="map-res">',
     '#suffix' => '</div>'
    ];


    //Panel button
    $build['search-map']['panel'] = [
      '#type' => 'markup',
      '#markup' => '<div id="panel"><button id="testButton" class="adc-button adc-sbutton">Bbox Filter</button><div class="current-bbox-filter"></div></div>',
      '#allowed_tags' => ['div','label','button'],
    ];

        //Message to be displayed under the map
        $build['search-map']['projection'] = [
          '#type' => 'markup',
          '#markup' => '<div class="proj-wrapper"><label class="proj-label">Select Projection</label></div>',
          '#allowed_tags' => ['div','label'],
      ];

      $build['search-map']['layers'] = [
        '#type' => 'markup',
        '#markup' => '<div class="layers-wrapper"></div>',
        '#allowed_tags' => ['div','label'],
    ];


        /* $build['suffix'] = [
          '#suffix' => '</div>'
        ];
        */

        //Set the cache for this form
        $build['#cache'] = [
    'contexts' => [
      'url.path',
      'url.query_args',
    ],
    ];

        // Add CSS and JS libraries and drupalSettings JS variables
        $build['#attached'] = [
    'library' => [
    'metsis_search/search_map_block',
    'metsis_lib/adc-button'
    ],
    'drupalSettings' => [
    'metsis_search_map_block' => [
      'mapLat' => $map_lat, //to be replaced with configuration variables
      'mapLon' => $map_lon, //to be replaced with configuration variables
      'mapZoom' => $map_zoom, //to be replaced with configuration variables
      'init_proj' => $map_init_proj, //to be replaced with configuration variables
      'additional_layers' => $map_additional_layers, //to be replaced with configuration variables
      'base_layer_wms_north' => $map_base_layer_wms_north,
      'base_layer_wms_south' => $map_base_layer_wms_south,
      'projections' => $map_projections,
      'layers_list' => $map_layers_list,
      'tllat' => $tllat,
      'tllon' => $tllon,
      'brlon' => $brlon,
      'brlat' => $brlat,
      'bboxFilter' => $bboxFilter,
      'mapFilter' => $map_filter,
      'pins' => $map_pins,
      'path' => $module_path,
      'extracted_info' => $extracted_info,
    ],
    ],
    ];

        //Set the id of the form
        /* $build['#attributes'] = [
          'id' => 'map-search',
        ];
        */

        return $build;
    }
}
