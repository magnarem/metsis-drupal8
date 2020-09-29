<?php
/*
 *
 * @file
 * Contains \Drupal\metsis_search\MetsisSearchConfigurationForm
 *
 * Form for Landing Page Creator Admin Configuration
 *
 **/
namespace Drupal\metsis_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;

/*
 *  * Class ConfigurationForm.
 *
 *  {@inheritdoc}
 *
 *   */
class MetsisSearchConfigurationForm extends ConfigFormBase {

  /*
   * {@inheritdoc}
  */
  protected function getEditableConfigNames() {
    return [
      'metsis_search.settings',
      ];
  }

  /*
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_search.admin_config_form';
  }

  /*
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metsis_search.settings');
    //$form = array();


// Choose view_mode for display landing page draft
$form['draft'] = [
  '#type' => 'fieldset',
  '#title' => 'Configure View mode for landing page draft',
  '#tree' => TRUE,
];
$form['draft']['view_mode'] = array(
  '#type' => 'select',
  '#options' => array(
  'default' => t('default'),
  'teaser' => t('teaser'),
  ),
  '#title' => t('Landing page draft view mode'),
  '#description' => t("Select which content type view mode to use for displaying landing page draft"),
  '#default_value' => $config->get('view_mode'),
);


    //$form['#attached']['library'][] = 'landing_page_creator/landing_page_creator';
    return parent::buildForm($form, $form_state);
 }

  /*
   * {@inheritdoc}
   *
   * NOTE: Implement form validation here
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    //get user and pass from admin configuration
    $values = $form_state->getValues();

  }

  /*
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /**
     * Save the configuration
    */
    $values = $form_state->getValues();

    $this->configFactory->getEditable('metsis_search.settings')
      ->set('username_datacite', $values['datacite']['username_datacite'])

      ->save();
    parent::submitForm($form, $form_state);
  }
}
