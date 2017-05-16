<?php

namespace Drupal\dmt_group_comments\Plugin\ModerationStateMachine;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\moderation_state_machine\ModerationStateMachineBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\activity_creator\Plugin\Type\ActivityActionManager;
use Drupal\content_moderation\StateTransitionValidation;
use Drupal\content_moderation\ModerationInformation;
use Drupal\activity_moderation\Plugin\Type\ActivityModerationManager;
use Drupal\group\GroupMembershipLoader;
use Drupal\Core\Session\AccountInterface;


/**
 * todo: this plugin is disabled we will enable it (remove status = 0)
 * when we figure out how to make ajax comments work with content_moderation
 * also remove comment_insert from dmt_group_comments.module once this is done
 *
 * Group Comments State Machine.
 *
 * @ModerationStateMachine(
 *  id = "group_comments",
 *  status = 0,
 *  entity_type = "comment",
 *  entity_bundle = "group_comments",
 *  label = @Translation("Group Comments"),
 * )
 */
class GroupCommentStateMachine extends ModerationStateMachineBase implements ContainerFactoryPluginInterface {

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateTransitionValidation $stateTransitionValidation, ModerationInformation $moderationInformation, GroupMembershipLoader $groupMembershipLoader, ActivityModerationManager $activityModerationManager, ActivityActionManager $activityActionManager, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $stateTransitionValidation, $moderationInformation, $account);

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
      $container->get('group.membership_loader'),
      $container->get('plugin.manager.activity_moderation_manager'),
      $container->get('plugin.manager.activity_action_processor'),
      $container->get('current_user')
    );
  }

  /**
   * On comment insert create Activity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function insert(ContentEntityInterface $entity) {
    $create_action = $this->activityActionManager->createInstance('create_activity_action');
    $create_action->create($entity);
  }

}
