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
    }

    // This should probably be extracted to a custom twig-template for this block.
    //<div class="map-div"><div class="geographical-search-div">

    /*
    <div class="projection-group">
     <label for="EPSG:4326">WGS:84</label>
     <input type="radio" id="EPSG:4326" name="map-search-projection" value="EPSG:4326" />
     <label for="EPSG:32661">UPS North</label>
     <input type="radio" id="EPSG:32661" name="map-search-projection" value="EPSG:32661" />
    <label for="EPSG:32761">UPS South</label>
     <input type="radio" id="EPSG:32761" name="map-search-projection" value="EPSG:32761" />
   </div>
   */
    $string = <<<EOM
        <div id="map-search" class="map-search">
        <br>
        <input type="radio" id="EPSG:4326" name="map-search-projection" value="EPSG:4326">WGS:84</input>
         <input type="radio" id="EPSG:32661" name="map-search-projection" value="EPSG:32661">UPS North</input>
         <input type="radio" id="EPSG:32761" name="map-search-projection" value="EPSG:32761">UPS South</input>
           <div id="droplayers">
             <button type="button" onclick="document.getElementById('lrs').classList.toggle('show')" class="layers-button">Layers</button>
             <div id="lrs" class="panel dropdown-lrs-content">
                <ul id="lrslist">
                <li class="addl"><input type="checkbox" value="europaveg">europaveg</li>
                <li class="addl"><input type="checkbox" value="riksveg">riksveg</li>
                <li class="addl"><input type="checkbox" value="fylkesveg">fylkesveg</li>
                </ul>
             </div>
           </div>


        </div>

        </div>
        <br>
           <br>
          <div id="map-search-message">Click on the map to draw a selection box. Boxes in UPS are transfomed into 4-edge polygons</div>
        <div class="form-item form-type-textfield form-item-bbox-top-left-lon">
          <input type="hidden" placeholder="" title="Top left longitude"  id="edit-bbox-top-left-lon" name="bbox_top_left_lon" value="" size="60" maxlength="128" class="form-text" />
        </div>
        <div class="form-item form-type-textfield form-item-bbox-top-left-lat">
          <input type="hidden" placeholder="" title="Top left latitude"  id="edit-bbox-top-left-lat" name="bbox_top_left_lat" value="" size="60" maxlength="128" class="form-text" />
        </div>
        <div class="form-item form-type-textfield form-item-bbox-bottom-right-lon|">
          <input type="hidden" placeholder="" title="Bottom right longitude" id="edit-bbox-bottom-right-lon" name="bbox_bottom_right_lon" value="" size="60" maxlength="128" class="form-text" />
        </div>
        <div class="form-item form-type-textfield form-item-bbox-bottom-right-lat">
          <input type="hidden" placeholder="" title="Bottom right latitude"  id="edit-bbox-bottom-right-lat" name="bbox_bottom_right_lat" value="" size="60" maxlength="128" class="form-text" />
        </div>
EOM;
    return [
      #'#type' => 'inline_template',
      #'#template' => '{{ mapdivs | raw }}',
      #'#context' => [
    #    'mapdivs' => "" //$string,
    #  ],
        #'#theme' => 'block__mapblockformetsissearch',
        '#markup' => "",
        '#tllat' => $tllat,
        '#tllon' => $tllon,
        '#brlon' => $brlon,
        '#brlat' => $brlat,
        '#attached' => [
          'library' => [
            'metsis_search/search_map'
          ],
          'drupalSettings' => [
            'metsis_search' => [
              'mapLat' => 69.659, //to be replaced with configuration variables
              'mapLon' => 18.984, //to be replaced with configuration variables
              'mapZoom' => 4.0, //to be replaced with configuration variables
              'init_proj' => 'EPSG:4326', //to be replaced with configuration variables
              'additional_layers' => FALSE, //to be replaced with configuration variables
            ],
          ],
        ],
        '#attributes' => [
              'id' => 'map-search',
      ],
    ];

  }
}
