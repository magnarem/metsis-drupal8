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
        \Drupal::logger('metsis_search')->debug("Building MapSearchBlock");
        //$tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
        //$bboxFilter = $tempstore->get('bboxFilter');
        $session = \Drupal::request()->getSession();
        $bboxFilter = $session->get('bboxFilter');
        $proj = $session->get('proj');

        //if ($bboxFilter != null) {
          $tllat = $session->get('tllat');
          $tllon = $session->get('tllon');
          $brlat = $session->get('brlat');
          $brlon = $session->get('brlon');
            /*
            $tllat = $tempstore->get('tllat');
            $tllon = $tempstore->get('tllon');
            $brlat = $tempstore->get('brlat');
            $brlon = $tempstore->get('brlon'); */
            \Drupal::logger('metsis_search_map_block')->debug("Got input filter vars: " .$tllat .','. $tllon .','.$brlat.','.$brlon);
        //}

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
        $pywps_service = $config->get('pywps_service');

        //Get the extracted info from tempstore
        //$tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
        $extracted_info = $session->get('extracted_info');
        //dpm($proj);
        if($proj != null) { $map_init_proj = $proj; }
        /**
         * Create the render array
         */

    /*     $build['map-res-popup'] = [
           '#markup' => '<div id="popup-map-res" class="ol-popup"><div id="popup-map-res-content"></div></div>',
         ];*/
        // search-map wrapper
        $build['search-map'] = [
          '#prefix' => '<div id="search-map" class="search-map w3-card-4">',
          '#suffix' => '</div>'
    ];

    // search-map wrapper
    $build['search-map']['panel'] = [
      '#prefix' => '<div id="panel" class="panel w3-container">',
      '#suffix' => '</div>'
];

$build['search-map']['panel']['basemap'] = [
  '#type' => 'markup',
  '#markup' => '<div class="basemap-wrapper"><label class="basemap-label"><strong>Select Basemap:</strong></label></div>',
  '#allowed_tags' => ['div','label','strong'],
];
//Message to be displayed under the map
$build['search-map']['panel']['projection'] = [
  '#type' => 'markup',
  '#markup' => '<div class="proj-wrapper"><label class="proj-label"><strong>Select Projection:</strong></label></div>',
  '#allowed_tags' => ['div','label','strong'],
];
    //Panel button
    $build['search-map']['panel']['buttons-container'] = [
      '#prefix' => '<div id="buttonsContainer" class="buttons-wrapper">',
      '#suffix' => '</div>'
    ];
    $build['search-map']['panel']['buttons-container']['bbox-filter'] = [
      '#type' => 'markup',
      '#markup' => '<span><button id="bboxButton" class="w3-left adc-button adc-sbutton">Create bounding box filter</button></span>',
      '#allowed_tags' => ['div','label','button','br','span'],
    ];

    $build['search-map']['panel']['buttons-container']['vis-all'] = [
      '#type' => 'markup',
      '#markup' => '<span id="vizClass"><button id="vizAllButton" class="w3-center adc-button adc-sbutton"></button></span>',
      '#allowed_tags' => ['div','label','button','br','span'],
    ];
    $build['search-map']['panel']['buttons-container']['reset'] = [
      '#type' => 'markup',
      '#markup' => '<span id="resetButtonID"><a id="resetButton" class="w3-center adc-button adc-sbutton" href="/metsis/search?op=Reset">Reset search</a></span>',
      '#allowed_tags' => ['div','label','button','br','a', 'span'],
    ];


    $build['search-map']['panel']['filter'] = [
      '#type' => 'markup',
      '#markup' => '<div class="current-bbox-filter"></div><div class="current-bbox-select"></div>',
      '#allowed_tags' => ['div','label','button','br'],
    ];



    $build['search-map']['panel']['layers'] = [
      '#type' => 'markup',
      '#markup' => '<div class="layers-wrapper"></div>',
      '#allowed_tags' => ['div','label'],
  ];


      $build['search-map']['map'] = [
        '#prefix' => '<div class="w3-border">',
        '#type' => 'markup',
        '#markup' => '<div id="map-res" class="map-res"></div>',
        '#suffix' => '</div>',
        '#allowed_tags' => ['div'],
    ];
    $build['search-map']['map']['popup'] = [
      '#prefix' => '<div id="popup" class="ol-popup" title="Select product:">',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div'],
    ];
    $build['search-map']['map']['popup']['closer'] = [
      '#type' => 'markup',
      '#markup' => '<a href="#" id="popup-closer" class="ol-popup-closer"></a>',
      '#allowed_tags' => ['a'],
    ];
    $build['search-map']['map']['popup']['content'] = [
      '#type' => 'markup',
      '#markup' => '<div id="popup-content" class="popup-content w3-small"></div>',
      '#allowed_tags' => ['div'],
    ];

    $build['search-map']['bottom-panel'] = [
      '#type' => 'markup',
      '#markup' => '<div id="bottomMapPanel" class="bottom-map-panel w3-panel"></div>',
      '#allowed_tags' => ['div'],
  ];

  //Placeholder for ts-plot
  $build['map-ts-plot'] = [
    '#prefix' => '<div id="bokeh-map-ts-plot" class="w3-card-2 w3-container">',
    '#suffix' => '</div>',
    '#allowed_tags' => ['div'],
  ];

  $build['map-ts-plot']['loader'] = [
    '#type' => 'markup',
    '#markup' => '<div class="map-ts-loader"></div>',
    '#allowed_tags' => ['div'],
  ];
  $build['map-ts-plot']['back'] = [
    '#type' => 'markup',
    '#markup' => '<div id="map-ts-back" class="map-ts-back"></div>',
    '#allowed_tags' => ['div'],
  ];
  $build['map-ts-plot']['variables'] = [
    '#type' => 'markup',
    '#markup' => '<div class="map-ts-vars"></div>',
    '#allowed_tags' => ['div'],
  ];
  $build['map-ts-plot']['plot'] = [
    '#type' => 'markup',
    '#markup' => '<div id="map-ts-plot" name="tsplot" class="map-ts-plot"></div>',
    '#allowed_tags' => ['div'],
  ];

        /* $build['suffix'] = [
          '#suffix' => '</div>'
        ];
        */

        //Set the cache for this form
        $build['#cache'] = [
          //'max-age' => 0,
         //'tags' =>$this->getCacheTags(),
          'contexts' => [
          //  'route',

              'url.path',
              'url.query_args',
            ],
          ];

        // Add CSS and JS libraries and drupalSettings JS variables
        $build['#attached'] = [
    'library' => [
    'metsis_search/search_map_block',
    'metsis_lib/adc-button',
    'metsis_ts_bokeh/style',
    'metsis_ts_bokeh/bokeh_js',
    'metsis_ts_bokeh/bokeh_widgets',
    'metsis_ts_bokeh/bokeh_tables',
    'metsis_ts_bokeh/bokeh_api',
    'blazy/load',
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
      'proj' => $proj,
      'bboxFilter' => $bboxFilter,
      'mapFilter' => $map_filter,
      'pins' => $map_pins,
      'path' => $module_path,
      'extracted_info' => $extracted_info,
      'pywps_service' => $pywps_service
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
    public function getCacheMaxAge() {
    return 1;
}
  }
