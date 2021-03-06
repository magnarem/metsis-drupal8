<?php

function metsis_qsearch_zero_form($form, &$form_state) {
  global $metsis_conf;
  $mqsearch_params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters();
  $full_text_search = '';
  $finished_after = '';
  $finished_before = '';
  $bbox_top_left_lat = '';
  $bbox_top_left_lon = '';
  $bbox_bottom_right_lon = '';
  $bbox_bottom_right_lat = '';
  $institutions = [];
  $collections = [];
  $cloud_cover_value = '';
  $investigator = '';
  $topics_and_variables = '';
  $c_platform = [];
  $c_s1a_cim = [];
  $c_s1a_cip = [];
  $c_s1b_cim = [];
  $c_s1b_cip = [];
  $c_s2a_cpt = [];
  $c_s2a_ccc = '';
  $c_s2b_cpt = [];
  $c_s2b_ccc = '';
  if (isset($mqsearch_params['quid'])) {
    $mq_cached_form_state_values = mq_db_select($mqsearch_params['quid']);
    $full_text_search = $mq_cached_form_state_values->chosen_full_text_search;
    $finished_after = $mq_cached_form_state_values->finished_after;
    $finished_before = $mq_cached_form_state_values->finished_before;
    $bbox_top_left_lat = $mq_cached_form_state_values->bbox_top_left_lat;
    $bbox_top_left_lon = $mq_cached_form_state_values->bbox_top_left_lon;
    $bbox_bottom_right_lon = $mq_cached_form_state_values->bbox_bottom_right_lon;
    $bbox_bottom_right_lat = $mq_cached_form_state_values->bbox_bottom_right_lat;
    foreach ($mq_cached_form_state_values->institutions->chosen_institutions as $i) {
      if ($i !== 0) {
        array_push($institutions, $i);
      }
    };
    if (property_exists('mq_cached_form_state_values', 'collections')) {
      foreach ($mq_cached_form_state_values->collections->chosen_collections as $i) {
        if ($i !== 0) {
          array_push($collections, $i);
        }
      }
    };
    if (isset($mq_cached_form_state_values->platform_long_name)) {
      $platform_long_names = get_object_vars($mq_cached_form_state_values->platform_long_name);
      foreach ($platform_long_names as $plna) {
        if (isset($plna->chosen_platform_long_name->sentinel_1a)) {
          if ($plna->chosen_platform_long_name->sentinel_1a === "sentinel_1a") {
            array_push($c_platform, "sentinel_1a");
            $c_s1a_cim = array_filter(get_object_vars($plna->instrument_modes->chosen_instrument_modes));
            $c_s1a_cip = array_filter(get_object_vars($plna->instrument_polarisations->chosen_instrument_polarisations));
          }
        } if (isset($plna->chosen_platform_long_name->sentinel_1b)) {
          if ($plna->chosen_platform_long_name->sentinel_1b === "sentinel_1b") {
            array_push($c_platform, "sentinel_1b");
            $c_s1b_cim = array_filter(get_object_vars($plna->instrument_modes->chosen_instrument_modes));
            $c_s1b_cip = array_filter(get_object_vars($plna->instrument_polarisations->chosen_instrument_polarisations));
          }
        } if (isset($plna->chosen_platform_long_name->sentinel_2a)) {
          if ($plna->chosen_platform_long_name->sentinel_2a === "sentinel_2a") {
            array_push($c_platform, "sentinel_2a");
            $c_s2a_cpt = array_filter(get_object_vars($plna->product_types->chosen_product_types));
            $c_s2a_ccc = $plna->cloud_cover_value->chosen_cloud_cover_value;
          }
        } if (isset($plna->chosen_platform_long_name->sentinel_2b)) {
          if ($plna->chosen_platform_long_name->sentinel_2b === "sentinel_2b") {
            array_push($c_platform, "sentinel_2b");
            $c_s2b_cpt = array_filter(get_object_vars($plna->product_types->chosen_product_types));
            $c_s2b_ccc = $plna->cloud_cover_value->chosen_cloud_cover_value;
          }
        }
      }
    } $investigator = $mq_cached_form_state_values->chosen_investigator;
    if (property_exists('mq_cached_form_state_values', 'chosen_topics_and_variables_a')) {
      $topics_and_variables = $mq_cached_form_state_values->chosen_topics_and_variables_a;
    }
  }
  $form['full_text_search'] = array(
    '#type' => 'fieldset',
    '#title' => defined('LABEL_FULL_TEXT') ? '<span class="adc_label">' . t(LABEL_FULL_TEXT) . '</span>' : t('Full text search'),
    '#collapsible' => TRUE,
    '#collapsed' => FULL_TEXT_SEARCH_INITIALLY_COLLAPSED,
    '#attributes' => array(
      'class' => array('full-text-search-fieldset')
    ),
  );

  $form['full_text_search']['chosen_full_text_search'] = array(
    '#type' => 'textfield',
    '#title' => defined('HINT_FULL_TEXT') ? '<div class="adc_hint">' . t(HINT_FULL_TEXT) . '</div>' : '',
    '#element_validate' => array('msb_text_qsearch_validate'),
    '#attributes' => array(
      'placeholder' => defined('PLACEHOLDER_FULL_TEXT') ? t(PLACEHOLDER_FULL_TEXT) : t('Search words'),
    ),
    '#default_value' => $full_text_search,
  );

  if (INVESTIGATOR_VISIBLE) {
    $form['investigator'] = array(
      '#type' => 'fieldset',
      '#title' => defined('LABEL_INVESTIGATOR') ? '<span class="adc_label">' . t(LABEL_INVESTIGATOR) . '</span>' : t('Investigator'),
      '#collapsible' => TRUE,
      '#collapsed' => INVESTIGATOR_INITIALLY_COLLAPSED,
      '#attributes' => array(
        'class' => array(
          'investigator-fieldset'
        )
      ),
    );
    $form['investigator']['chosen_investigator'] = array(
      '#type' => 'textfield',
      '#title' => defined('HINT_INVESTIGATOR') ? '<div class="adc_hint">' . t(HINT_INVESTIGATOR) . '</div>' : '',
      '#element_validate' => array(
        'adc_investigator_validate'
      ),
      '#attributes' => array(
        'placeholder' => defined('PLACEHOLDER_INVESTIGATOR') ? t(PLACEHOLDER_INVESTIGATOR) : t('Investigator\'s name'),
      ),
      '#default_value' => $investigator,
    );
  } if (TOPICS_AND_VARIABLES_VISIBLE) {
    $form['topics_and_variables'] = array(
      '#type' => 'fieldset',
      '#title' => defined('LABEL_TOPICS_AND_VARIABLES') ? '<span class="adc_label">' . t(LABEL_TOPICS_AND_VARIABLES) . '</span>' : t('Topics and variables'),
      '#collapsible' => TRUE,
      '#collapsed' => TOPICS_AND_VARIABLES_INITIALLY_COLLAPSED,
      '#attributes' => array(
        'class' => array(
          'topics-and-variables-fieldset'
        )
      ),
    );
    $form['topics_and_variables']['chosen_topics_and_variables_a'] = array(
      '#type' => 'textfield',
      '#title' => defined('HINT_TOPICS_AND_VARIABLES') ? '<div class="adc_hint">' . t(HINT_TOPICS_AND_VARIABLES) . '</div>' : '',
      '#autocomplete_path' => METADATA_PREFIX . 'keywords/autocomplete',
      '#element_validate' => array(
        'msb_text_qsearch_validate'
      ),
      '#attributes' => array(
        'placeholder' => defined('PLACEHOLDER_TOPICS_AND_VARIABLES') ? t(PLACEHOLDER_TOPICS_AND_VARIABLES) : t('Investigator\'s name'),
      ),
      '#default_value' => $topics_and_variables,
    );
  }
  $form['operational_status'] = array(
    '#access' => FALSE,
    '#type' => 'hidden',
    '#title' => t('Operational status'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#attributes' => array('class' => array('operational-status-fieldset',),
      'title' => hack_get_skos_opstat("Operational Status"),
    ),
  );
  $form['operational_status']['chosen_operational_status'] = array(
    '#type' => 'checkboxes',
    '#options' => array_combine(msb_get_operational_statuses(), msb_get_operational_statuses()),
  );
  if (INSTITUTIONS_VISIBLE) {
    $form['institutions'] = array(
      '#type' => 'fieldset',
      //'#title' => t('Institutions'),
      '#title' => defined('LABEL_INSTITUTIONS') ? '<span class="adc_label">' . t(LABEL_INSTITUTIONS) . '</span>' : t('Institutions'),
      '#collapsible' => TRUE,
      '#collapsed' => INSTITUTIONS_INITIALLY_COLLAPSED,
      '#tree' => TRUE,
      '#attributes' => array(
        'class' => array('institutions-fieldset')),
    );
    $form['institutions']['chosen_institutions'] = array('#type' => 'checkboxes', '#options' => array_combine(msb_get_institutions(), msb_get_institutions()), '#default_value' => $institutions,);
  } if (COLLECTIONS_VISIBLE) {
    $form['collections'] = array(
      '#type' => 'fieldset',
      '#title' => defined('LABEL_COLLECTIONS') ? '<span class="adc_label">' . t(LABEL_COLLECTIONS) . '</span>' : t('Collections'),
      '#collapsible' => TRUE,
      '#collapsed' => COLLECTIONS_INITIALLY_COLLAPSED,
      '#tree' => TRUE,
      '#attributes' => array(
        'class' => array(
          'collections-fieldset'
        )
      ),
    );
    $form['collections']['chosen_collections'] = array('#type' => 'checkboxes', '#options' => array_combine(msb_facet_get_collections(), msb_facet_get_collections()), '#default_value' => $collections,);
  } $platform_long_name_array = q_get_platform_long_name();
  ksort($platform_long_name_array);
  if (defined('PRODUCT_TYPES')) {
    $product_types_array = explode(',', PRODUCT_TYPES);
    sort($product_types_array);
  } if (defined('INSTRUMENT_MODES')) {
    $instrument_modes_array = explode(',', INSTRUMENT_MODES);
    sort($instrument_modes_array);
  } if (defined('INSTRUMENT_POLARISATIONS')) {
    $instrument_polarisations_array = explode(',', INSTRUMENT_POLARISATIONS);
    sort($instrument_polarisations_array);
  } if (PLATFORM_LONG_NAME_VISIBLE) {
    $form['platform_long_name'] = array(
      '#type' => 'fieldset',
      //'#title' => t('Platform'), 
      '#title' => defined('LABEL_PLATFORM_LONG_NAME') ? '<span class="adc_label">' . t(LABEL_PLATFORM_LONG_NAME) . '</span>' : t('Platform'),
      '#collapsible' => TRUE,
      '#collapsed' => PLATFORM_LONG_NAME_INITIALLY_COLLAPSED,
      '#tree' => TRUE,
      '#attributes' => array(
        'class' => array('platform_long_name-fieldset')
      ),
    );
    foreach ($platform_long_name_array as $k => $v) {
      $form['platform_long_name'][$k]['chosen_platform_long_name'] = array('#type' => 'checkboxes', '#options' => array_combine(array($k), array($v)), '#default_value' => array_values($c_platform), '#attributes' => array('class' => array('platform-long-name-check-box',),),);
      if ($k == 'sentinel_1a') {
        if (INSTRUMENT_MODES_VISIBLE) {
          $form['platform_long_name'][$k]['instrument_modes'] = array('#type' => 'fieldset', '#title' => t('Mode'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#tree' => TRUE,);
          $form['platform_long_name'][$k]['instrument_modes']['chosen_instrument_modes'] = array('#type' => 'checkboxes', '#options' => array_combine(array_values($instrument_modes_array), array_values($instrument_modes_array)), '#default_value' => $c_s1a_cim,);
          if (INSTRUMENT_POLARISATION_VISIBLE) {
            $form['platform_long_name'][$k]['instrument_polarisations'] = array('#type' => 'fieldset', '#title' => t('Polarisation'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#tree' => TRUE,);
            $form['platform_long_name'][$k]['instrument_polarisations']['chosen_instrument_polarisations'] = array('#type' => 'checkboxes', '#options' => array_combine(array_values($instrument_polarisations_array), array_values($instrument_polarisations_array)), '#default_value' => $c_s1a_cip,);
          }
        }
      } if ($k == 'sentinel_1b') {
        if (INSTRUMENT_MODES_VISIBLE) {
          $form['platform_long_name'][$k]['instrument_modes'] = array('#type' => 'fieldset', '#title' => t('Mode'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#tree' => TRUE,);
          $form['platform_long_name'][$k]['instrument_modes']['chosen_instrument_modes'] = array('#type' => 'checkboxes', '#options' => array_combine(array_values($instrument_modes_array), array_values($instrument_modes_array)),);
          if (INSTRUMENT_POLARISATION_VISIBLE) {
            $form['platform_long_name'][$k]['instrument_polarisations'] = array('#type' => 'fieldset', '#title' => t('Polarisation'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#tree' => TRUE,);
            $form['platform_long_name'][$k]['instrument_polarisations']['chosen_instrument_polarisations'] = array('#type' => 'checkboxes', '#options' => array_combine(array_values($instrument_polarisations_array), array_values($instrument_polarisations_array)),);
          }
        }
      } if ($k == 'sentinel_2a') {
        if (PRODUCT_TYPES_VISIBLE) {
          $form['platform_long_name'][$k]['product_types'] = array('#type' => 'fieldset', '#title' => t('Product type'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#tree' => TRUE,);
          $form['platform_long_name'][$k]['product_types']['chosen_product_types'] = array('#type' => 'checkboxes', '#options' => array_combine(array_values($product_types_array), array_values($product_types_array)),);
        } if ($metsis_conf['cloud_cover_value_visible'] === TRUE) {
          $form['platform_long_name'][$k]['cloud_cover_value'] = array('#type' => 'fieldset', '#title' => t('Cloud cover [%] of scene'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#tree' => TRUE, '#attributes' => array('class' => array('cloud_cover_value-fieldset')),);
          $form['platform_long_name'][$k]['cloud_cover_value']['chosen_cloud_cover_value'] = array('#type' => 'radios', '#options' => array_combine($metsis_conf['cloud_cover_value_search_options'], $metsis_conf['cloud_cover_value_search_options']), '#default_value' => $c_s2a_ccc,);
        }
      } if ($k == 'sentinel_2b') {
        if (PRODUCT_TYPES_VISIBLE) {
          $form['platform_long_name'][$k]['product_types'] = array('#type' => 'fieldset', '#title' => t('Product type'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#tree' => TRUE,);
          $form['platform_long_name'][$k]['product_types']['chosen_product_types'] = array('#type' => 'checkboxes', '#options' => array_combine(array_values($product_types_array), array_values($product_types_array)),);
        } if ($metsis_conf['cloud_cover_value_visible'] === TRUE) {
          $form['platform_long_name'][$k]['cloud_cover_value'] = array('#type' => 'fieldset', '#title' => t('Cloud cover [%] of scene'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#tree' => TRUE, '#attributes' => array('class' => array('cloud_cover_value-fieldset')),);
          $form['platform_long_name'][$k]['cloud_cover_value']['chosen_cloud_cover_value'] = array('#type' => 'radios', '#options' => array_combine($metsis_conf['cloud_cover_value_search_options'], $metsis_conf['cloud_cover_value_search_options']), '#default_value' => $c_s2b_ccc,);
        }
      }
    }
  } global $metsis_conf;
  $jquery_path = "/sites/all/modules/jquery_update/replace/jquery/1.10/jquery.min.js";
  $form['geographical_search'] = array(
    '#type' => 'fieldset',
    '#title' => defined('LABEL_GEOGRAPHIC_EXTENT') ? '<span class="adc_label">' . t(LABEL_GEOGRAPHIC_EXTENT) . '</span>' : t('Geographical search'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#attached' => array(
      'js' => array($jquery_path,),
    ),
    '#attributes' => array(
      'class' => array(
        'geographical-search-fieldset',),
    ),
  );
  $form['data_collection_period'] = array(
    '#type' => 'fieldset',
    '#title' => defined('LABEL_TEMPORAL_EXTENT') ? '<span class="adc_label">' . t(LABEL_TEMPORAL_EXTENT) . '</span>' : t('Data collection period'),
    '#collapsible' => TRUE,
    '#collapsed' => DATA_COLLECTION_PERIOD_INITIALLY_COLLAPSED,
    '#attributes' => array(
      'class' => array('data-collection-period-fieldset',),
    ),
  );
  if ($finished_after != '') {
    $default_finished_after = $finished_after;
  }
  elseif ($metsis_conf['default_start_date']) {
    $default_finished_after = $metsis_conf['default_start_date'];
  }
  else {
    $default_finished_after = msb_get_short_isodate(adc_get_now_minus_hours(SEARCH_MAX_METADATA_AGE));
  } if ($finished_before != '') {
    $default_finished_before = $finished_before;
  }
  elseif ($metsis_conf['default_end_date']) {
    $default_finished_before = $metsis_conf['default_end_date'];
  }
  else {
    $default_finished_before = "";
  }
  $form['data_collection_period'][] = array(
    '#type' => 'item',
    'finished_after' => array('#type' => 'date_popup',
      '#title' => defined('HINT_TEMPORAL_EXTENT_START_DATE') ? '<div class="adc_hint">' . t(HINT_TEMPORAL_EXTENT_START_DATE) . '</div>' : t('Start date'),
      '#default_value' => $default_finished_after,
      '#date_format' => 'Y-m-d',
      '#date_year_range' => '-50:+2',
      '#datepicker_options' => array('changeMonth' => TRUE, 'changeYear' => TRUE,),
      '#element_validate' => array('msb_start_finish_date_validate'),
      '#attributes' => array(
        'placeholder' => defined('PLACEHOLDER_TEMPORAL_EXTENT_START_DATE') ? t(PLACEHOLDER_TEMPORAL_EXTENT_START_DATE) : t('yyyy-mm-dd'),
        'class' => array('adc-highlight',),
        'title' => "Start date BEFORE or FROM",),),
    'finished_before' => array('#type' => 'date_popup',
      '#title' => defined('HINT_TEMPORAL_EXTENT_END_DATE') ? '<div class="adc_hint">' . t(HINT_TEMPORAL_EXTENT_END_DATE) . '</div>' : t('End date'),
      '#default_value' => $default_finished_before,
      '#date_format' => 'Y-m-d',
      '#date_year_range' => '-50:+2',
      '#datepicker_options' => array(),
      '#element_validate' => array('msb_start_finish_date_validate'),
      '#attributes' => array(
        'placeholder' => defined('PLACEHOLDER_TEMPORAL_EXTENT_START_DATE') ? t(PLACEHOLDER_TEMPORAL_EXTENT_END_DATE) : t('yyyy-mm-dd'),
        'class' => array('adc-highlight',),
        'title' => "End date FROM or AFTER",)
    ,)
    ,);
  $form['bounding_box'] = array(
    '#type' => 'fieldset',
    '#title' => t('Bounding box'),
    '#title' => defined('LABEL_BOUNDING_BOX') ? '<span class="adc_label">' . t(LABEL_BOUNDING_BOX) . '</span>' : t('Bounding box'),
    '#collapsible' => TRUE,
    '#collapsed' => BOUNDING_BOX_INITIALLY_COLLAPSED,
    '#attributes' => array(
      'class' => array('bounding-box-fieldset',),
    ),
  );
  $form['bounding_box'][] = array(
    '#type' => 'item',
    '#title' => defined('HINT_TOP_LEFT_LONGITUDE') ? '<div class="adc_hint">' . t(HINT_TOP_LEFT_LONGITUDE) . '</div>' : t('Top left longitude'),
    'bbox_top_left_lon' => array(
      '#type' => 'textfield',
      '#element_validate' => array('adc_longitude_validate'),
      '#attributes' => array(
        'placeholder' => defined('PLACEHOLDER_TOP_LEFT_LONGITUDE') ? t(PLACEHOLDER_TOP_LEFT_LONGITUDE) : t('Top left longitude'),
        'title' => "Top left longitude",),
      '#default_value' => $bbox_top_left_lon,
    ),
    'bbox_top_left_lat' => array(
      '#type' => 'textfield',
      '#element_validate' => array('adc_latitude_validate'),
      '#title' => defined('HINT_TOP_LEFT_LATITUDE') ? '<div class="adc_hint">' . t(HINT_TOP_LEFT_LATITUDE) . '</div>' : t('Top left latitude'),
      '#attributes' => array(
        'placeholder' => defined('PLACEHOLDER_TOP_LEFT_LATITUDE') ? t(PLACEHOLDER_TOP_LEFT_LATITUDE) : t('Top left latitude'),
        'title' => "Top left latitude",),
      '#default_value' => $bbox_top_left_lat,
    ),
    'bbox_bottom_right_lon' => array(
      '#type' => 'textfield',
      '#element_validate' => array('adc_longitude_validate'),
      '#title' => defined('HINT_BOTTOM_RIGHT_LONGITUDE') ? '<div class="adc_hint">' . t(HINT_BOTTOM_RIGHT_LONGITUDE) . '</div>' : t('Bottom right longitude'),
      '#attributes' => array(
        'placeholder' => defined('PLACEHOLDER_BOTTOM_RIGHT_LONGITUDE') ? t(PLACEHOLDER_BOTTOM_RIGHT_LONGITUDE) : t('Bottom right longitude'),
        'title' => "Bottom right longitude",),
      '#default_value' => $bbox_bottom_right_lon,
    ),
    'bbox_bottom_right_lat' => array(
      '#type' => 'textfield',
      '#element_validate' => array('adc_latitude_validate'),
      '#title' => defined('HINT_BOTTOM_RIGHT_LATITUDE') ? '<div class="adc_hint">' . t(HINT_BOTTOM_RIGHT_LATITUDE) . '</div>' : t('Bottom right latitude'),
      '#default_value' => $bbox_bottom_right_lat,
      '#attributes' => array(
        'placeholder' => defined('PLACEHOLDER_BOTTOM_RIGHT_LATITUDE') ? t(PLACEHOLDER_BOTTOM_RIGHT_LATITUDE) : t('Bottom right latitude'),
        'title' => "Bottom right latitude",
      ),
    ),
  );
  $form['geographical_search']['map'] = array('#prefix' => ' ', '#markup' => adc_get_geographical_search_map(), '#suffix' => ' ',);
  $form['#validate'][] = 'msb_all_or_none_latlon_validate';
  $form['#validate'][] = 'msb_foo_validate';
  $form['submit'] = array('#type' => 'submit', '#value' => t('Search'), '#submit' => array('metsis_qsearch_submit'),);
  if (FORM_RESET_VISIBLE) {
    $form['options']['reset'] = array('#type' => 'submit', '#value' => t('Reset'), '#submit' => array('metsis_qsearch_form_reset'),);
  } if (BASKET_ELEMENTS_VISIBLE) {
    $user = \Drupal::currentUser();
    if (($user->uid) && get_user_item_count($user->uid) > 0) {
      $form['goto_basket'] = array(
        '#type' => 'submit',
        '#value' => t('Basket (@basket_item_count)',
          array(
            '@basket_item_count' => get_user_item_count($user->uid))),
        '#submit' => array(
          'adc_goto_basket'
        ),
        '#validate' => array(),
        '#attributes' => array(
          'class' => array(
            'adc-button-small',
          ),
        ),
      );
      $form['empty_basket'] = array('#type' => 'submit', '#value' => t('Empty basket'), '#submit' => array('adc_empty_basket'), '#validate' => array(), '#attributes' => array('class' => array('adc-button-small',),),);
    }
  } $path = drupal_get_path('module', 'metsis_qsearch');
  $form['#attached'] = array(
    'css' => array('type' => 'file', 'data' => $path . '/css/metsis_qsearch.css',),
    'js' => array('type' => 'file', 'data' => $path . '/js/metsis_qsearch.js',),
  );
  //$form['#attributes'] = array('OnSubmit' => 'alert("Submission interruption");');
  return $form;
}

function metsis_qsearch_submit($form, &$form_state) {

  $initial_user_query = msb_get_final_solr_qq($form_state);
  $_SESSION['qsearch']['initial_user_query'] = $initial_user_query;
  $bytes = 32;
  $quid = bin2hex(openssl_random_pseudo_bytes($bytes)) . (string) time();
  $_SESSION['qsearch']['quid'] = $quid;
  mq_db_insert(mq_get_fields($form_state['values'], $_SESSION['qsearch']['quid'], session_id()));
  $form_state['redirect'] = array('results/', array('query' => array('page' => 1,),),);
}

function qsearch_results_page() {
  $params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters();
  return q_get_paged_form();
}

function theme_metsis_qsearch_form($variables) {
  global $metsis_conf;
  $form = $variables['form'];
  $output = '';
  $output .= '<div class="non-map-div">';
  $output .= '<div class="full-text-search-div">';
  $output .= \Drupal::service("renderer")->render($form['full_text_search']);
  $output .= '</div>';
  if (COLLECTION_PERIOD_VISIBLE) {
    $output .= '<div class="data-collection-period-div">';
    $output .= \Drupal::service("renderer")->render($form['data_collection_period']);
    $output .= '</div>';
  } $output .= '<div class="bounding-box-div">';
  $output .= \Drupal::service("renderer")->render($form['bounding_box']);
  $output .= '</div>';
  if (INSTITUTIONS_VISIBLE) {
    $output .= '<div class="institutions-div">';
    $output .= \Drupal::service("renderer")->render($form['institutions']);
    $output .= '</div>';
  } if (COLLECTIONS_VISIBLE) {
    $output .= '<div class="collections-div">';
    $output .= \Drupal::service("renderer")->render($form['collections']);
    $output .= '</div>';
  } if (PLATFORM_LONG_NAME_VISIBLE) {
    $output .= '<div class="platform_long_name-div">';
    $output .= \Drupal::service("renderer")->render($form['platform_long_name']);
    $output .= '</div>';
  } $output .= '<div class="habeli initially-hidden">';
  $output .= \Drupal::service("renderer")->render($form['yyplatform_long_name']['sentinel_2b']['chosen_product_types']);
  $output .= '</div>';
  if (INVESTIGATOR_VISIBLE) {
    $output .= '<div class="investigator-div">';
    $output .= \Drupal::service("renderer")->render($form['investigator']);
    $output .= '</div>';
  } if (TOPICS_AND_VARIABLES_VISIBLE) {
    $output .= '<div class="topics-and-variables-div">';
    $output .= \Drupal::service("renderer")->render($form['topics_and_variables']);
    $output .= '</div>';
  } $output .= '<div class="operational-status-div">';
  $output .= \Drupal::service("renderer")->render($form['operational_status']);
  $output .= '</div>';
  $output .= '<div class="form-action-div">';
  $output .= \Drupal::service("renderer")->render($form['submit']);
  $output .= \Drupal::service("renderer")->render($form['options']['reset']);
  if (FORM_RESET_VISIBLE) {
    $output .= \Drupal::service("renderer")->render($form['reset']);
  } if (BASKET_ELEMENTS_VISIBLE) {
    $user = \Drupal::currentUser();
    if (($user->uid) && get_user_item_count($user->uid) > 0) {
      $output .= \Drupal::service("renderer")->render($form['goto_basket']);
      $output .= \Drupal::service("renderer")->render($form['empty_basket']);
    }
  }
  $output .= '</div>';
  $output .= '</div>';
  $output .= '<div class="map-div">';
  $output .= '<div class="geographical-search-div">';
  $output .= \Drupal::service("renderer")->render($form['geographical_search']);
  $output .= '</div>';
  $output .= '</div>';
  $output .= drupal_render_children($form);
  return $output;
}
