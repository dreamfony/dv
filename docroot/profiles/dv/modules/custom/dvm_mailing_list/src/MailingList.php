<?php

namespace Drupal\dvm_mailing_list;

use Drupal\activity_creator\Plugin\Type\ActivityActionManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
use Drupal\group\GroupMembershipLoaderInterface;
use Drupal\group\GroupMembership;
use Drupal\user\Entity\User;


/**
 * Class Mailing List
 * @package Drupal\dvm_mailing_list
 */
class MailingList {

  /**
   * @var string
   */
  protected $mailingListType;

  /**
   * @var string
   */
  protected $mailingListLabel;

  /**
   * @var GroupMembershipLoaderInterface
   */
  protected $groupMembershipLoader;

  /**
   * @var \Drupal\activity_creator\Plugin\Type\ActivityActionManager
   */
  protected $activityActionProcessor;


  /**
   * MailingList constructor.
   *
   * @param \Drupal\group\GroupMembershipLoaderInterface $group_membership_loader
   * @param \Drupal\activity_creator\Plugin\Type\ActivityActionManager $activity_action_manager
   */
  public function __construct(GroupMembershipLoaderInterface $group_membership_loader, ActivityActionManager $activity_action_manager) {
    $this->groupMembershipLoader = $group_membership_loader;
    $this->activityActionProcessor = $activity_action_manager;

    $this->mailingListLabel = 'New Survey';
    $this->mailingListType = 'mailing_list';
  }

  /**
   * Create new Mailing List.
   *
   * @return int|mixed|null|string
   */
  public function createMailingList() {

    $emptyGroup = $this->getUsersEmptyGroup();

    if (!$emptyGroup) {
      $group = Group::create([
        'label' => $this->mailingListLabel,
        'type' => $this->mailingListType
      ]);

      $group->save();

      return $group->id();
    }

    return $emptyGroup->id();
  }

  /**
   * Check if user already has empty Survey
   *
   * @return bool|\Drupal\group\Entity\Group
   */
  protected function getUsersEmptyGroup() {
    $query = \Drupal::entityQuery('group');
    $query->condition('type', $this->mailingListType);
    $query->condition('label', $this->mailingListLabel);
    $query->condition('uid', \Drupal::currentUser()->id());
    $result = $query->execute();

    if($result) {
      $group = Group::load(reset($result));
      $group_content = $group->getContent('group_node:question');
      $group_users = $group->getMembers([$this->mailingListType . '-organisation']);
      if (empty($group_content) AND empty($group_users)) {
        return $group;
      }
    }

    return FALSE;
  }

  /**
   * Adds recipients to Mailing List.
   *
   * @param array $gids
   * @param $mailing_list_id
   */
  public function addRecipients(array $gids, $mailing_list_id) {

    $mailing_list_group = Group::load( $mailing_list_id );

    foreach ($gids as $gid) {
      $gid = $gid['target_id'];

      if ($gid) {
        /** @var Group $group */
        $group = Group::load($gid);
        $membership = $group->getMembers([$group->bundle() . '-organisation']);

        foreach ($membership as $membershipgc) {
          /** @var GroupMembership $membershipgc */
          $org_uids[] = $membershipgc->getGroupContent()->getEntity()->id();

          foreach ($org_uids as $org_uid) {
            $org_user = User::load($org_uid);
            $mailing_list_group->addMember($org_user, ['group_roles' => ['mailing_list-organisation']]);
          }

        }
      }
    }

  }

  /**
   * Send Mailing List
   *
   * @param \Drupal\group\Entity\Group $group
   */
  public function sendForApproval(Group $group) {

    // remove administrator role
    $account = $group->getOwner();
    $group_membership = $this->groupMembershipLoader->load($group, $account);

    $group_content = $group_membership->getGroupContent();

    /** @var EntityReferenceFieldItemList $group_roles */
    $group_roles = $group_content->get('group_roles');

    foreach ($group_roles->referencedEntities() as $delta => $role) {
      /** @var EntityInterface $role */
      if ($role->id() == 'mailing_list-administrator') {
        $group_roles->removeItem($delta);
        break;
      }
    }
    $group_content->set('group_roles', $group_roles->referencedEntities());

    // save group membership
    $group_content->save();

    // set moderation state
    $group->set('moderation_state', 'email');
    $group->save();
  }

  /**
   * Approve.
   *
   * @param \Drupal\group\Entity\Group $group
   */
  public function approve(Group $group) {
    $group_content_questions = $group->getContent('group_node:question');

    foreach ($group_content_questions as $group_content) {
      /** @var GroupContent $group_content */
      $activity_entity = Node::load($group_content->getEntity()->id());
      $data['group_id'] = $group_content->getGroup()->id();

      $create_action = $this->activityActionProcessor->createInstance('email_organisation_action');
      $create_action->create($activity_entity, $data);
    }

    // set moderation state
    $group->set('moderation_state', 'published');
    $group->save();

  }

}
