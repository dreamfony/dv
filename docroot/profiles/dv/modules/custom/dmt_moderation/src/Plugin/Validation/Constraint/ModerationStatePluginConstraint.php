<?php

namespace Drupal\dmt_moderation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Entity Moderation Constraint.
 *
 * @Constraint(
 *   id = "ModerationStatePlugin",
 *   label = @Translation("Entity Moderation", context = "Validation"),
 * )
 */
class ModerationStatePluginConstraint extends Constraint {

  /**
   * Message shown when an anonymous node is being created.
   *
   * @var string
   */
  public $message;

}
