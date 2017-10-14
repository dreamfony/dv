<?php

namespace Drupal\dmt_mailing_list\Plugin\ModerationStateMachine;

use Drupal\activity_creator\Plugin\Type\ActivityActionManager;
use Drupal\activity_moderation\Plugin\Type\ActivityModerationManager;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dmt_mailing_list_activity\MailingListActivity;
use Drupal\moderation_state_machine\ModerationStateMachineBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\group\GroupMembershipLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group\Entity\GroupContent;

/**
 * Provides a 'ModerationAction' activity action.
 *
 * @ModerationStateMachine(
 *  id = "approve_sending",
 *  label = @Translation("Mailing List Approve Sending Transition"),
 *  transition_id = "approve_sending",
 *  entity_type = "group",
 *  entity_bundle = "mailing_list",
 * )
 */
class MailingListApproveSendingTransition extends ModerationStateMachineBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\dmt_mailing_list_activity\MailingListActivity
   */
  protected $mailingListActivity;

  /**
   * @var \Drupal\group\GroupMembershipLoader
   */
  protected $groupMembershipLoader;

  /**
   * @var \Drupal\activity_moderation\Plugin\Type\ActivityModerationManager
   */
  protected $activityModerationManager;

  /**
   * @var \Drupal\activity_creator\Plugin\Type\ActivityActionManager
   */
  protected $activityActionManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailingListActivity $mailingListActivity, GroupMembershipLoader $groupMembershipLoader, ActivityModerationManager $activityModerationManager, ActivityActionManager $activityActionManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mailingListActivity = $mailingListActivity;
    $this->groupMembershipLoader = $groupMembershipLoader;
    $this->activityModerationManager = $activityModerationManager;
    $this->activityActionManager = $activityActionManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dmt_mailing_list_activity.mailing_list_activity'),
      $container->get('group.membership_loader'),
      $container->get('plugin.manager.activity_moderation_manager'),
      $container->get('plugin.manager.activity_action_processor')
    );
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return array
   */
  public function validate(ContentEntityInterface $entity) {
    $violations = [];

    // prevent sending for approval with a message
    if($this->mailingListActivity->allActivitiesCount($entity->id()) == 0) {
      $violations[] = [
        'message' => 'Please add content and recipients before sending for approval.',
        'cause' => 'allow_link' // send this cause if you want to ignore this violation when showing links
      ];
    }

    return $violations;
  }

  public function switch(ContentEntityInterface $entity) {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $entity;

    $group_content_contents = $group->getContent('group_node:content');

    foreach ($group_content_contents as $group_content) {
      /** @var GroupContent $group_content */
      $activity_entity = $group_content->getEntity();
      $data['group_id'] = $group->id();
      $data['context'] = 'organisation_activity_context';
      $create_action = $this->activityActionManager->createInstance('create_activity_action');
      $create_action->create($activity_entity, $data);
    }

    /** @var \Drupal\activity_moderation\Plugin\ActivityModeration\OpenModerationTicket $create_action */
    $activity_moderation = $this->activityModerationManager->createInstance('close_mailing_list_ticket');
    $activity_moderation->closeModerationActivity($group);
  }

}
