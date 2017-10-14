<?php

namespace Drupal\moderation_state_machine;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines an interface for Activity action plugins.
 */
interface ModerationStateMachineInterface extends PluginInspectionInterface {

  /**
   * Call switch method in corresponding plugin.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function switch(ContentEntityInterface $entity);

}
