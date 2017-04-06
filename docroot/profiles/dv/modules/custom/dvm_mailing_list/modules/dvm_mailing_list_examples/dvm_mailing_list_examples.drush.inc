<?php

/**
 * Implements hook_drush_command().
 */
function dvm_mailing_list_examples_drush_command() {
  $items['mailing-list-examples'] = array(
    'description' => dt('Add Mailing list examples.'),
    'examples' => array(
      "drush mle" => "Generates example mailing lists.",
    ),
    'aliases' => array('mle'),
  );
  return $items;
}
/**
 * Add demo content.
 */
function drush_dvm_mailing_list_examples_mailing_list_examples() {
    // Create an instance of the necessary class.
    $mailing_list_examples = \Drupal::service('dvm_mailing_list_examples.mailing_list_examples');
    $mailing_list_examples->createContent();

    drush_print("Mailing lists created.");

}
