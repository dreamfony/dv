<?php

namespace Drupal\moderation_state_machine;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\ContentEntityInterface;


/**
 * Base class for Switch Moderation State plugins.
 */
abstract class ModerationStateMachineBase extends PluginBase implements ModerationStateMachineInterface {

  /**
   * @inheritdoc
   */
  public function validate(ContentEntityInterface $entity) {

  }

  /**
   * @inheritdoc
   */
  public function switch(ContentEntityInterface $entity) {

  }

}
