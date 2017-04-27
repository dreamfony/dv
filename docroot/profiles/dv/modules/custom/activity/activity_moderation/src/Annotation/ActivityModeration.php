<?php

/**
 * @file
 * Contains \Drupal\activity_creator\Annotation\ActivityAction.
 */

namespace Drupal\activity_moderation\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Activity Moderation item annotation object.
 *
 * @see \Drupal\activity_moderation\Plugin\Type\ActivityModerationManager
 * @see plugin_api
 *
 * @Annotation
 */
class ActivityModeration extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;


  public $message_type;

}
