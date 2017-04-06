<?php

/**
 * Implements hook_drush_command().
 */
function dmt_demo_mailing_list_drush_command() {
  $items['demo-mailing-list'] = array(
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
function drush_dmt_demo_mailing_list_demo_mailing_list() {
    // Create an instance of the necessary class.
    $mailing_list_examples = \Drupal::service('dmt_demo_mailing_list.mailing_list_examples');
    $mailing_list_examples->createContent();

    drush_print("Mailing lists created.");

}
