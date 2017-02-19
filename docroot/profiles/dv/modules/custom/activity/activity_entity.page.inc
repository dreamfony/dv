<?php

/**
 * @file
 * Contains activity_entity.page.inc.
 *
 * Page callback for Activity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Activity templates.
 *
 * Default template: activity_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_activity_entity(array &$variables) {
  // Fetch ActivityEntity Entity Object.
  $activity_entity = $variables['elements']['#activity_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
