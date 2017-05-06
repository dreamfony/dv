<?php

/**
 * @file
 * Contains \Drupal\activity_creator\Annotation\ActivityAction.
 */

namespace Drupal\moderation_state_machine\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Moderation State Machine item annotation object.
 *
 * @see \Drupal\moderation_state_machine\Plugin\Type\ModerationStateMachineManager
 * @see plugin_api
 *
 * @Annotation
 */
class ModerationStateMachine extends Plugin {

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

  /**
   * Plugin weight
   *
   * @var integer
   */
  public $weight;

}
