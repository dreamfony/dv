<?php

namespace Drupal\activity_send_email\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ActivityEmailSendConfigForm.
 *
 * @package Drupal\activity_send_email\Form
 */
class ActivityEmailSendConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'activity_send_email.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'activity_email_send_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('activity_send_email.config');

    $form['replyto'] = [
      '#type' => 'email',
      '#title' => $this->t('Reply To'),
      '#description' => $this->t('Reply to email address adds hash.'),
      '#maxlength' => 64,
      '#size' => 35,
      '#default_value' => $config->get('replyto'),
      '#required' => TRUE,
    ];

    $form['noreply'] = [
      '#type' => 'email',
      '#title' => $this->t('No Reply'),
      '#description' => $this->t('No Reply address.'),
      '#maxlength' => 64,
      '#size' => 35,
      '#default_value' => $config->get('noreply'),
      '#required' => TRUE,
    ];

    $form['filterstring'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Filter String'),
      '#description' => $this->t('String used for inbox filtering'),
      '#maxlength' => 12,
      '#size' => 12,
      '#default_value' => $config->get('filterstring'),
      '#required' => TRUE,
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

    $this->config('activity_send_email.config')
      ->set('replyto', $form_state->getValue('replyto'))
      ->set('noreply', $form_state->getValue('noreply'))
      ->set('filterstring', $form_state->getValue('filterstring'))
      ->save();
  }

}
