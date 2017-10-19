<?php

namespace Drupal\moderation_state_machine\Plugin\ModerationStateMachine;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\moderation_state_machine\ModerationStateMachineBase;

/**
 * Default switch state plugin is a fall back.
 *
 * @ModerationStateMachine(
 *  id = "moderation_state_machine_default",
 *  label = @Translation("Switch Moderation State Default"),
 * )
 */
class ModerationStateMachineDefault extends ModerationStateMachineBase {

  public function switch (ContentEntityInterface $entity) {
    // TODO: Implement switch() method.
  }
}
