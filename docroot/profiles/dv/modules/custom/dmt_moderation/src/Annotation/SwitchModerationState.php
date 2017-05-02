<?php

/**
 * @file
 * Contains \Drupal\activity_creator\Annotation\ActivityAction.
 */

namespace Drupal\dmt_moderation\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Switch Moderation State item annotation object.
 *
 * @see \Drupal\dmt_moderation\Plugin\Type\SwitchModerationStateManager
 * @see plugin_api
 *
 * @Annotation
 */
class SwitchModerationState extends Plugin {

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

  /**
   * Entity Type
   *
   * @var string
   */
  public $entity_type;

  /**
   * Entity Bundle
   *
   * @var string
   */
  public $entity_bundle;

}
