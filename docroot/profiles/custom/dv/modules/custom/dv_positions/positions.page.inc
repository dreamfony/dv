<?php

/**
 * @file
 * Contains positions.page.inc.
 *
 * Page callback for Positions entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Positions templates.
 *
 * Default template: positions.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_positions(array &$variables) {
  // Fetch Positions Entity Object.
  $positions = $variables['elements']['#positions'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
