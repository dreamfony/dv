<?php

namespace Drupal\dvm_mailing_list\Plugin\SwitchModerationState;

use Drupal\activity_creator\Plugin\Type\ActivityActionManager;
use Drupal\activity_moderation\Plugin\Type\ActivityModerationManager;
use Drupal\content_moderation\ModerationInformation;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\dmt_moderation\SwitchModerationStateBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dvm_mailing_list\MailingList;
use Drupal\group\GroupMembershipLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\content_moderation\StateTransitionValidation;
use Drupal\group\Entity\GroupContent;

/**
 * Provides a 'ModerationAction' activity action.
 *
 * @SwitchModerationState(
 *  id = "dvm_mailing_list",
 *  entity_type = "group",
 *  entity_bundle = "mailing_list",
 *  label = @Translation("Switch Moderation State"),
 * )
 */
class SwitchModerationState extends SwitchModerationStateBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\dvm_mailing_list\MailingList
   */
  protected $mailingList;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateTransitionValidation $stateTransitionValidation, ModerationInformation $moderationInformation, MailingList $mailingList, GroupMembershipLoader $groupMembershipLoader, ActivityModerationManager $activityModerationManager, ActivityActionManager $activityActionManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $stateTransitionValidation, $moderationInformation);

    $this->mailingList = $mailingList;
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
      $container->get('content_moderation.state_transition_validation'),
      $container->get('content_moderation.moderation_information'),
      $container->get('dvm_mailing_list.mailing_list'),
      $container->get('group.membership_loader'),
      $container->get('plugin.manager.activity_moderation_manager'),
      $container->get('plugin.manager.activity_action_processor')
    );
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return array
   */
  public function approve_sending_validate(ContentEntityInterface $entity, AccountInterface $account) {
    $violations = [];

    // prevent sending for approval with a message
    if($this->mailingList->allActivitiesCount($entity->id()) == 0) {
      $violations[] = 'Please add questions and recipients before sending for approval.';
    }

    return $violations;
  }

  public function approve_sending_switch(ContentEntityInterface $entity, AccountInterface $account) {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $entity;

    $group_content_questions = $group->getContent('group_node:question');

    foreach ($group_content_questions as $group_content) {
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

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return array
   */
  public function send_email_validate(ContentEntityInterface $entity, AccountInterface $account) {
    $violations = [];

    // prevent sending for approval with a message
    if($this->mailingList->allActivitiesCount($entity->id()) == 0) {
      $violations[] = 'Please add questions and recipients before sending for approval.';
    }

    return $violations;
  }

  public function send_email_switch(ContentEntityInterface $entity, AccountInterface $account) {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $entity;

    // remove administrator role
    $account = $group->getOwner();
    $group_membership = $this->groupMembershipLoader->load($group, $account);

    $group_content = $group_membership->getGroupContent();

    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $group_roles */
    $group_roles = $group_content->get('group_roles');

    foreach ($group_roles->referencedEntities() as $delta => $role) {
      /** @var ContentEntityInterface $role */
      if ($role->id() == 'mailing_list-administrator') {
        $group_roles->removeItem($delta);
        break;
      }
    }

    // send message to moderator
    /** @var \Drupal\activity_moderation\Plugin\ActivityModeration\OpenModerationTicket $create_action */
    $activity_moderation = $this->activityModerationManager->createInstance('open_moderation_ticket');
    $activity_moderation->createModerationActivity($group);
  }

}
