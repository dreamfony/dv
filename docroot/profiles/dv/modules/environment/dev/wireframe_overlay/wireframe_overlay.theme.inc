<?php

use Drupal\block_content\BlockContentInterface;
use Drupal\block_content\Entity\BlockContent;

function wireframe_overlay_preprocess_html(&$variables) {

  $bid = 'wireframe_overlay';

  /** @var BlockContentInterface $block */
  $block = BlockContent::load($bid)->getUntranslated();




  // $block_content = \Drupal::service('block')->load("ttter");

  // $variables['#attached']['drupalSettings']['wireframe_overlay']['block'] = $block_content;

}
