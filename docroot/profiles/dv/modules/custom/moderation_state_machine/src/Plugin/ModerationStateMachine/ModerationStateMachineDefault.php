<?php

namespace Drupal\moderation_state_machine\Plugin\ModerationStateMachine;

use Drupal\moderation_state_machine\ModerationStateMachineBase;

/**
 * Default switch state plugin is a fall back.
 *
 * @ModerationStateMachine(
 *  id = "switch_moderation_state_default",
 *  label = @Translation("Switch Moderation State Default"),
 *  entity_type = "n/a",
 *  entity_bundle = "n/a"
 * )
 */
class ModerationStateMachineDefault extends ModerationStateMachineBase { }
