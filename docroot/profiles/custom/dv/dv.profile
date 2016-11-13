<?php

/**
 * @file
 * Enables modules and site configuration for a dv site installation.
 */

use Drupal\contact\Entity\ContactForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function dv_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  $form['#submit'][] = 'dv_form_install_configure_submit';
}

/**
 * Submission handler to sync the contact.form.feedback recipient.
 */
function dv_form_install_configure_submit($form, FormStateInterface $form_state) {
  $site_mail = $form_state->getValue('site_mail');
  ContactForm::load('feedback')->setRecipients([$site_mail])->trustData()->save();
}

/**
 * Implements hook_install_tasks().
 */
function dv_install_tasks(&$install_state) {
  $tasks = array(
    'dv_install_final_setup' => array(
      'display_name' => t('Do install finalization tasks'),
      'type' => 'batch',
    ),
  );
  return $tasks;
}

function dv_install_final_setup(&$install_state) {
  node_access_rebuild();
  // Generate demo content.

  // run cron
  \Drupal::service('cron')->run();

}