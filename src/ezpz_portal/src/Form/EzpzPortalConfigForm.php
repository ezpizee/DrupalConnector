<?php

namespace Drupal\ezpz_portal\Form;

include_once explode(DIRECTORY_SEPARATOR.'ezpz'.DIRECTORY_SEPARATOR, __DIR__)[0].DIRECTORY_SEPARATOR.'ezpz'.
  DIRECTORY_SEPARATOR.'ezpzlib'.DIRECTORY_SEPARATOR.'autoload.php';

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Ezpizee\ConnectorUtils\Client;

/**
 * Configure RSS settings for this site
 *
 * @internal
 */
class EzpzPortalConfigForm extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'ezpz_portal_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $ezpzPortalConfig = $this->config('ezpz_portal.settings');
    $form['client_id'] = [
      '#type'          => 'textfield',
      '#title'         => t('Client ID'),
      '#default_value' => $form_state->getValue(Client::KEY_CLIENT_ID, $ezpzPortalConfig->get(Client::KEY_CLIENT_ID)),
      '#description'   => $this->t('Ezpizee\'s Client ID, which can be obtained from www.ezpizee.com'),
      '#required'      => TRUE,
      '#size'          => 32,
      '#maxlength'     => 32
    ];
    $form['client_secret'] = [
      '#type'          => 'textfield',
      '#title'         => t('Client Secret'),
      '#default_value' => $form_state->getValue(Client::KEY_CLIENT_SECRET, $ezpzPortalConfig->get(Client::KEY_CLIENT_SECRET)),
      '#description'   => $this->t('Ezpizee\'s Client Secret, which can be obtained from www.ezpizee.com'),
      '#required'      => TRUE,
      '#size'          => 32,
      '#maxlength'     => 32
    ];
    $form['app_name'] = [
      '#type'          => 'textfield',
      '#title'         => t('App Name'),
      '#default_value' => $form_state->getValue(Client::KEY_APP_NAME, $ezpzPortalConfig->get(Client::KEY_APP_NAME)),
      '#description'   => $this->t('You can name any unique name, for this installation, which hasn\'t been used in any installation yet'),
      '#required'      => TRUE,
      '#size'          => 60,
      '#maxlength'     => 255
    ];
    $env = $form_state->getValue(Client::KEY_ENV, $ezpzPortalConfig->get(Client::KEY_ENV));
    $options = ['' => 'Select environment', 'local' => 'Local', 'dev' => 'Development', 'stage' => 'Staging', 'prod' => 'Production'];
    $form['env'] = [
      '#type'          => 'select',
      '#title'         => t('Environment'),
      '#default_value' => $env ? $env : 'prod',
      '#options'       => $options,
      '#description'   => t('Ezpizee environment which you are connecting to or integrate with. Default: Production'),
      '#required'      => TRUE
    ];
    $protocol = $form_state->getValue('protocol', $ezpzPortalConfig->get('protocol'));
    $options = ['' => 'Select a protocol', 'http://' => 'http://', 'https://' => 'https://'];
    $form['protocol'] = [
      '#type'          => 'select',
      '#title'         => t('Protocol'),
      '#default_value' => $protocol ? $protocol : 'https://',
      '#options'       => $options,
      '#description'   => t('The protocol that you will connect to Ezpizee environment. Default: https://'),
      '#required'      => TRUE
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $data = [
      Client::KEY_CLIENT_ID     => $form_state->getValue(Client::KEY_CLIENT_ID),
      Client::KEY_CLIENT_SECRET => $form_state->getValue(Client::KEY_CLIENT_SECRET),
      Client::KEY_APP_NAME      => $form_state->getValue(Client::KEY_APP_NAME),
      Client::KEY_ENV           => $form_state->getValue(Client::KEY_ENV),
      'protocol'                => $form_state->getValue('protocol')
    ];

    $tokenHandler = 'Drupal\ezpz_api\Controller\ContextProcessors\TokenHandler';
    $response = Client::install(Client::DEFAULT_ACCESS_TOKEN_KEY, $data, $tokenHandler);

    if (!empty($response)) {
      if (isset($response['code']) && (int)$response['code'] !== 200) {
        if ($response['message'] === 'ITEM_ALREADY_EXISTS') {
          Drupal::messenger()->addMessage('App Name is already taken. Give a different name and try again.');
          parent::submitForm($form, $form_state);
        }
        else {
          Drupal::messenger()->addMessage($response['message']);
        }
      }
      else {
        $this->config('ezpz_portal.settings')
          ->set('client_id', $form_state->getValue('client_id'))
          ->set('client_secret', $form_state->getValue('client_secret'))
          ->set('app_name', $form_state->getValue('app_name'))
          ->set('env', $form_state->getValue('env'))
          ->set('protocol', $form_state->getValue('protocol'))
          ->save();

        parent::submitForm($form, $form_state);
      }
    }
    else {
      Drupal::messenger()->addMessage('Failed to install Ezpizee App');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return ['ezpz_portal.settings'];
  }

}
