<?php

namespace Drupal\dvm_mailing_list;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
use Drupal\activity_creator\Plugin\ActivityActionManager;
use Drupal\group\GroupMembershipLoaderInterface;

/**
 * Class Mailing List
 * @package Drupal\dvm_mailing_list
 */
class MailingList {

  /**
   * @var GroupMembershipLoaderInterface
   */
  protected $groupMembershipLoader;

  /**
   * @var \Drupal\activity_creator\Plugin\ActivityActionManager
   */
  protected $activityActionProcessor;


  /**
   * @param GroupMembershipLoaderInterface $group_membership_loader
   * @param \Drupal\activity_creator\Plugin\ActivityActionManager $activity_action_processor
   */
  public function __construct(GroupMembershipLoaderInterface $group_membership_loader, ActivityActionManager $activity_action_processor) {
    $this->groupMembershipLoader = $group_membership_loader;
    $this->activityActionProcessor = $activity_action_processor;
  }

  /**
   * Create new Mailing List.
   *
   * @return int|mixed|null|string
   */
  public function createMailingList() {

    /// @todo Check if user already has empty Survey

    $group = Group::create([
      'label' => 'New Survey',
      'type' => 'mailing_list'
    ]);

    $group->save();

    return $group->id();
  }

  /**
   * Send Mailing List
   *
   * @param \Drupal\group\Entity\Group $group
   */
  public function sendForApproval(Group $group) {

    $account = $group->getOwner();
    $group_membership = $this->groupMembershipLoader->load($group, $account);

    $group_content = $group_membership->getGroupContent();

    // remove administrator role
    /** @var EntityReferenceFieldItemList $group_roles */
    $group_roles = $group_content->get('group_roles');

    foreach ($group_roles->referencedEntities() as $delta => $role) {
      /** @var EntityInterface $role */
      if($role->id() == 'mailing_list-administrator') {
        $group_roles->removeItem($delta);
        break;
      }
    }
    $group_content->set('group_roles', $group_roles->referencedEntities());

    // save group membership
    $group_content->save();
  }

  /**
   * Approve.
   *
   * @param \Drupal\group\Entity\Group $group
   */
  public function approve(Group $group) {

    /// @todo: check what happens to deleted entities due to multiversion
    $group_content_questions = $group->getContent('group_node:question');

    foreach ($group_content_questions as $group_content) {
      /** @var GroupContent $group_content */
      $activity_entity = Node::load($group_content->getEntity()->id());
      $data['group_id'] = $group_content->getGroup()->id();

      $create_action = $this->activityActionProcessor->createInstance('email_organisation_action');
      $create_action->create($activity_entity, $data);
    }
  }
}
