<?php

function metsis_basket_visualize_action_info() {
  $action = [
    'metsis_basket_visualize_action' => [
      'type' => 'entity',
      'label' => t('ADC visualize items'),
      'behavior' => ['changes_property'],
      'configurable' => FALSE,
      'vbo_configurable' => FALSE,
      'pass rows' => TRUE,
      'triggers' => ['any'],
      'aggregate' => TRUE,
    ],
  ];
  return $action;
}

function metsis_basket_visualize_action(&$node, $context) {
  $metadata_identifiers = [];
  foreach ($node as $n) {
    $metadata_identifiers[] = $n->metadata_identifier;
  }
  $solr_core = adc_get_solr_core([$metadata_identifiers[0]]);
  $options = [
    'query' => [
      'dataset' => implode(",", $metadata_identifiers),
      'solr_core' => $solr_core[$metadata_identifiers[0]],
      'calling_results_page' => BASKET_ENDPOINT,
    ],
  ];
  if (count($metadata_identifiers > 1)) {
    //\Drupal::messenger()->addWarning('Time series plotting for basket items is not fully implemented.');
  }
  if (adc_has_feature_type($metadata_identifiers[0], 'timeSeries') === 1) {
    $options['query']['metadata_identifier'] = $metadata_identifiers[0];
    drupal_goto(TS_ENDPOINT, $options);
  }
  else {
    drupal_goto(WMS_ENDPOINT, $options);
  }
}
