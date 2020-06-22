<?php /**
 * @file
 * Contains \Drupal\metsis_wms\Controller\DefaultController.
 */

namespace Drupal\metsis_wms\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Default controller for the metsis_wms module.
 */
class DefaultController extends ControllerBase {

  public function get_custom_content() {
    $datasetURL = filter_input(INPUT_GET, "datasetURL");
    //var_dump($datasetURL);
    $content = [
    '#markup' => '<div class="map container"><div id="map"></div><div id="lyr-switcher"></div>' . '<div id="proj-container"></div><div id="timeslider-container"></div></div>' . '<div id="wmsURL" class="element-hidden">' . $datasetURL . '</div>',
  ];
    return $content;
  }

}
