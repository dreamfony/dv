<?php

namespace Drupal\geonames\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class GeonamesConfigForm.
 *
 * @package Drupal\geonames\Form
 */
class GeonamesConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'geonames.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'geonames_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('geonames.config');
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#description' => $this->t('GeoNames Username.'),
      '#maxlength' => 64,
      '#size' => 35,
      '#default_value' => $config->get('username'),
      '#required' => TRUE,
    ];

    $server = $config->get('server');
    $form['server'] = [
      '#type' => 'radios',
      '#title' => $this->t('Server'),
      '#description' => $this->t('Choose a server.'),
      '#options' => [
        'http://api.geonames.org' => $this->t('Free (http://api.geonames.org)'),
        'http://ws.geonames.net' => $this->t('Payed (http://ws.geonames.net)'),
      ],
      '#default_value' => !empty($server) ? $server : 'http://api.geonames.org',
      '#required' => TRUE,
    ];

    $form['token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#description' => $this->t('GeoNames Token.'),
      '#maxlength' => 64,
      '#size' => 35,
      '#default_value' => $config->get('token'),
      '#states' => [
        'visible' => [
          ':input[name="server"]' => ['value' => 'http://ws.geonames.net'],
        ],
        'required' => [
          ':input[name="server"]' => ['value' => 'http://ws.geonames.net'],
        ],
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('geonames.config')
      ->set('username', $form_state->getValue('username'))
      ->set('server', $form_state->getValue('server'))
      ->set('token', $form_state->getValue('token'))
      ->save();
  }

}
