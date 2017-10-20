<?php

namespace Drupal\moderation_state_machine\Plugin\ModerationStateMachine;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\moderation_state_machine\ModerationStateMachineBase;

/**
 * Example plugin for "Editorial workflow".
 * Possible transitions are create_new_draft, publish, archive, archived_draft and archived_published
 *
 * @ModerationStateMachine(
 *  id = "page_state_machine",
 *  transition_id = "publish",
 *  entity_type = "node",
 *  entity_bundle = "page",
 *  label = @Translation("Switch Moderation State"),
 * )
 */
class PageStateMachine extends ModerationStateMachineBase {

  /**
   * Validates publish transition
   * TransitionId_validate
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return array
   */
  public function validate(ContentEntityInterface $entity) {
    $violations = [];
    $current_time = strtotime('now');
    if ($current_time > strtotime('12:00am') && $current_time < strtotime('08:00am')) {
      $violations[] = [
        'message' => "Please don't publish pages while you moonwalk.",
        'cause' => 'allow_link'
        // send this cause if you want to ignore this violation when showing links
      ];
    }

    return $violations;
  }

  /**
   * Act on publish transition
   * TransitionId_switch
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */

  public function switchTransition(ContentEntityInterface $entity) {
    drupal_set_message(t("Page is published!"));
  }

}
