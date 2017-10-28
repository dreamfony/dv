<?php

function wireframe_overlay_preprocess_html(&$variables) {

  $block_content = \Drupal::service('block')->load("ttter");

  $variables['#attached']['drupalSettings']['wireframe_overlay']['block'] = $block_content;

}
