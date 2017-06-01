<?php

namespace Drupal\moderation_state_machine;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\workflows\WorkflowInterface;

/**
 * Workflow Chain.
 */
class WorkflowChain {

  /**
   * Get Transition.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param \Drupal\workflows\WorkflowInterface $workflow
   * @return \Drupal\workflows\Transition|\Drupal\workflows\TransitionInterface
   */
  public function getTransition(ContentEntityInterface $entity, WorkflowInterface $workflow) {
    $original_entity = $entity->original;

    $transitions = $workflow->getTransitions();

    // check if user can transition entity
    foreach ($transitions as $transition) {
      /** @var \Drupal\workflows\Transition $transition */
      if ($entity->moderation_state->value != $original_entity->moderation_state->value && $entity->moderation_state->value == $transition->to()
          ->id()
      ) {
        foreach ($transition->from() as $from_state) {
          /** @var \Drupal\workflows\State $from_state */
          if ($from_state->id() == $original_entity->moderation_state->value) {
            return $transition;
          }
        }

      }
    }

  }

}
