<?php

namespace Drupal\dmt_moderation\Plugin\ModerationStateMachine;

use Drupal\dmt_moderation\ModerationStateMachineBase;

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
