<?php

namespace Drupal\moderation_state_machine;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\ContentEntityInterface;


/**
 * Base class for Switch Moderation State plugins.
 */
abstract class ModerationStateMachineBase extends PluginBase implements ModerationStateMachineInterface {

  /**
   *
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function validate(ContentEntityInterface $entity) {

  }

  /**
   * @inheritdoc
   */
  abstract function switchTransition(ContentEntityInterface $entity);

}
