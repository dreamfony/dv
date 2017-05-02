<?php

namespace Drupal\dmt_moderation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Entity Moderation Constraint.
 *
 * @Constraint(
 *   id = "EntityModeration",
 *   label = @Translation("Entity Moderation", context = "Validation"),
 * )
 */
class EntityModerationConstraint extends Constraint {

  /**
   * Message shown when an anonymous node is being created.
   *
   * @var string
   */
  public $message;

}
