<?php

function metsis_basket_empty_action_info() {
  $action = array('empty_action' => array('type' => 'entity', 'label' => t('ADC Empty basket'), 'behavior' => array('changes_property'), 'configurable' => TRUE, 'vbo_configurable' => FALSE, 'pass rows' => TRUE, 'triggers' => array('any'), 'aggregate' => TRUE,),);
  return $action;
}

function empty_action_form($context, &$form_state) {
  $vbo = _views_bulk_operations_get_field($form_state['build_info']['args'][0]);
  $selections = [];
  $selections[0] = 1;
  for ($i = 0; $i < count($form_state['build_info']['args'][0]->result); $i++) {
    
  } $form = array();
  return $form;
}

function empty_action_submit($form, &$form_state) {
  empty_metsis_db_records($form_state['build_info']['args'][0]->base_table);
}

function empty_metsis_db_records($table) {
  $user = \Drupal::currentUser();
  \Drupal::database()->delete($table)->condition('uid', $user->uid)->execute();
}

function empty_action_form_alter($from, $form_state, $form_id) {
  
}
