<?php

namespace Drupal\training_entity_config;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;

class SiteAnnouncementForm extends EntityForm{
  public  function form(array $form, FormStateInterface $formState) {
    $form = parent::form($form, $formState);
    /*
     *
     */
    $entity = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => t('label'),
      '#required' => TRUE,
      '#default_value' => $entity->label(),
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => t('Message'),
      '#required' => TRUE,
      '#default_value' => $entity->getMessage(),
    ];
    return $form;
  }

  public function save(array $form, FormStateInterface $formState) {
    $entity = $this->entity;
    $is_new = !$entity->getOriginalId();
    if ($is_new) {
      $machine_name = \Drupal::transliteration()->transliterate($entity->label(),LanguageInterface::LANGCODE_DEFAULT.'_');
      $entity->set('id', Unicode::strtolower($machine_name));

      drupal_set_message(t('The %label announcement has been created.', array('%label' => $entity->label())));
    }
    else {
      drupal_set_message(t('Updated the %label announcement.', array('%label' => $entity->label())));
    }
    $entity->save();

    $formState->setRedirectUrl($this->entity->toUrl('collection'));
  }
}