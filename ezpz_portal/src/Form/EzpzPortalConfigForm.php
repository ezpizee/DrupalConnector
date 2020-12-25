<?php

namespace Drupal\ezpz_portal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure RSS settings for this site
 *
 * @internal
 */
class EzpzPortalConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ezpz_portal_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ezpz_portal.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $ezpzPortalConfig = $this->config('ezpz_portal.settings');
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => t('Client ID'),
      '#default_value' => $ezpzPortalConfig->get('client_id'),
      '#description' => t('Ezpizee\'s Client ID, which can be obtained from www.ezpizee.com'),
      '#required' => TRUE,
      '#size' => 32,
      '#maxlength' => 32
    ];
    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => t('Client Secret'),
      '#default_value' => $ezpzPortalConfig->get('client_secret'),
      '#description' => t('Ezpizee\'s Client Secret, which can be obtained from www.ezpizee.com'),
      '#required' => TRUE,
      '#size' => 32,
      '#maxlength' => 32
    ];
    $form['app_name'] = [
      '#type' => 'textfield',
      '#title' => t('App Name'),
      '#default_value' => $ezpzPortalConfig->get('app_name'),
      '#description' => t('You can name any unique name, for this installation, which hasn\'t been used in any installation yet'),
      '#required' => TRUE,
      '#size' => 60,
      '#maxlength' => 255
    ];
    $env = $ezpzPortalConfig->get('env');
    $options = [''=>'Select environment', 'local'=>'Local', 'dev'=>'Development', 'stage'=>'Staging', 'prod'=>'Production'];
    $form['env'] = [
      '#type' => 'select',
      '#title' => t('Environment'),
      '#default_value' => $env ? $env : 'prod',
      '#options' => $options,
      '#description' => t('Ezpizee environment which you are connecting to or integrate with. If not sure, pick Production'),
      '#required' => TRUE
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('ezpz_portal.settings')
      ->set('client_id', $form_state->getValue('client_id'))
      ->set('client_secret', $form_state->getValue('client_secret'))
      ->set('app_name', $form_state->getValue('app_name'))
      ->set('env', $form_state->getValue('env'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
