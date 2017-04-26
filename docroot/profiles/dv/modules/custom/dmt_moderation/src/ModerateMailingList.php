<?php

namespace Drupal\dmt_moderation;


use Drupal\group\Entity\Group;
use Drupal\activity_creator\Entity\Activity;
use Drupal\activity_creator\Plugin\Type\ActivityActionManager;


/// @todo: fix typos activities not activites
/// @todo: rename class to Moderation

class ModerateMailingList {


  /**
   * @var \Drupal\activity_creator\Plugin\Type\ActivityActionManager
   */
  protected $activityActionProcessor;

  public function __construct(ActivityActionManager $activity_action_manager) {
    $this->activityActionProcessor = $activity_action_manager;
  }

  /// @todo: rename createModerationActivity accepts EntityInterface $entity
  public function openModerationTicket(Group $group) {
//    Create Activity
    $create_action = $this->activityActionProcessor->createInstance('moderation_action');
    $create_action->create($group);
  }

  /// @todo: same as above
  public function closeModerationTicket(Group $group) {
    $activites = $this->GetRelatedActivites($group->id(), 'open');
    foreach ($activites as $activity) {
      /** @var Activity $activity */
      // $activity->setModerationState('closed');
      $this->changeActivityState($activity);
    }
  }

 /**
  *  We actually never know which event exactly created which activity.
  *  Probably in more complex scenario we could create event entity and use UUID
  */
  private function getRelatedActivites($group_id, $status, $bundle = 'moderation_activity') {
    $query = \Drupal::entityQuery('activity')
        /// $query->addTag($entity_type.'_'.$entity_bundle):
        /// $query->addMetaData($entity_id);
        /// and then implement dvm_mailing_list_query_group_mailing_list_alter
        /// to add this first condition
      ->condition('field_activity_mailing_list.target_id', $group_id)
      ->condition('type', $bundle);

    if ($status) {
      /** @see activity_creator_query_cm_states_alter */
      $query->addTag('cm_states');
      $query->addMetaData('states', [$status]);
    }

    $activites = $query->execute();

    return $activites;
  }

  /* @see: line 38 Activity Entitiy already has that method */
  private function changeActivityState(Activity $activity, $state = 'closed') {

  }
}
