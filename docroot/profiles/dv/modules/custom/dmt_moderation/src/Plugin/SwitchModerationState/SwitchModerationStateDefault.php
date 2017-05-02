<?php

namespace Drupal\dmt_moderation\Plugin\SwitchModerationState;

use Drupal\dmt_moderation\SwitchModerationStateBase;

/**
 * Default switch state plugin is a fall back.
 *
 * @SwitchModerationState(
 *  id = "switch_moderation_state_default",
 *  label = @Translation("Switch Moderation State Default"),
 *  entity_type = "n/a",
 *  entity_bundle = "n/a"
 * )
 */
class SwitchModerationStateDefault extends SwitchModerationStateBase {

}
