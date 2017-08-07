<?php

namespace Drupal\moderation_state_machine;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\workflows\Transition;
use Drupal\content_moderation\StateTransitionValidation as StateTransitionValidationCore;

/**
 * Validates whether a certain state transition is allowed.
 */
class StateTransitionValidation extends StateTransitionValidationCore {

  /**
   * {@inheritdoc}
   */
  public function getValidTransitions(ContentEntityInterface $entity, AccountInterface $user) {
    $workflow = $this->moderationInfo->getWorkflowForEntity($entity);
    $current_state = $entity->moderation_state->value ? $workflow->getState($entity->moderation_state->value) : $workflow->getTypePlugin()->getInitialState($workflow, $entity);

    return array_filter($current_state->getTransitions(), function(Transition $transition) use ($workflow, $user, $entity) {

      if($user->hasPermission('use ' . $workflow->id() . ' transition ' . $transition->id())) {
        return TRUE;
      }
      $transition1 = $transition->id();
      $test1 = $user->hasPermission('owner can use ' . $workflow->id() . ' transition ' . $transition->id());
      $test2 = $entity->getOwnerId() == $user->id();

      if(
        $user->hasPermission('owner can use ' . $workflow->id() . ' transition ' . $transition->id()) &&
        $entity->getOwnerId() == $user->id()
      ) {
        return TRUE;
      }

      return FALSE;
    });
  }

}
