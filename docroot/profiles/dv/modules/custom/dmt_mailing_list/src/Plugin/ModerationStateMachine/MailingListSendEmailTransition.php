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


/**
 * Provides a 'ModerationAction' activity action.
 *
 * @ModerationStateMachine(
 *  id = "send_email",
 *  label = @Translation("Mailing List Send Email Transition"),
 *  transition_id = "send_email",
 *  entity_type = "group",
 *  entity_bundle = "mailing_list",
 * )
 */
class MailingListSendEmailTransition extends ModerationStateMachineBase implements ContainerFactoryPluginInterface {

  /**
   * @var MailingListActivity
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
   * {@inheritdoc}
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

  /**
   * {@inheritdoc}
   */
  public function switch(ContentEntityInterface $entity) {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $entity;

    // remove administrator role from the group
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

    // set group roles
    $group_content->set('group_roles', $group_roles->referencedEntities());
    // save group membership
    $group_content->save();

    // send message to moderator
    /** @var \Drupal\activity_moderation\Plugin\ActivityModeration\OpenModerationTicket $create_action */
    $activity_moderation = $this->activityModerationManager->createInstance('open_moderation_ticket');
    $activity_moderation->createModerationActivity($group);
  }

}
