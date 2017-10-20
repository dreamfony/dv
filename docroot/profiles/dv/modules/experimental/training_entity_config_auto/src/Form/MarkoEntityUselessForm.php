<?php

namespace Drupal\training_entity_config_auto\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MarkoEntityUselessForm.
 */
class MarkoEntityUselessForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $marko_entity_useless = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $marko_entity_useless->label(),
      '#description' => $this->t("Label for the Marko entity useless."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $marko_entity_useless->id(),
      '#machine_name' => [
        'exists' => '\Drupal\training_entity_config_auto\Entity\MarkoEntityUseless::load',
      ],
      '#disabled' => !$marko_entity_useless->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $marko_entity_useless = $this->entity;
    $status = $marko_entity_useless->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Marko entity useless.', [
          '%label' => $marko_entity_useless->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Marko entity useless.', [
          '%label' => $marko_entity_useless->label(),
        ]));
    }
    $form_state->setRedirectUrl($marko_entity_useless->toUrl('collection'));
  }

}
