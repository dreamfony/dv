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
use Drupal\panelizer\PanelizerInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Cache\CacheBackendInterface;


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
   * @var \Drupal\panelizer\PanelizerInterface
   */
  protected $panelizer;

  /**
   * @var CacheBackendInterface
   *   Cache backend.
   */
  protected $cacheBackend;

  /**
   * @var \Drupal\dvm_mailing_list\MailingListAnswer
   */
  protected $mailingListAnswer;


  /**
   * MailingList constructor.
   *
   * @param \Drupal\group\GroupMembershipLoaderInterface $group_membership_loader
   * @param \Drupal\activity_creator\Plugin\Type\ActivityActionManager $activity_action_manager
   * @param \Drupal\panelizer\PanelizerInterface $panelizer
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   */
  public function __construct(GroupMembershipLoaderInterface $group_membership_loader, ActivityActionManager $activity_action_manager, PanelizerInterface $panelizer, CacheBackendInterface $cache_backend, MailingListAnswer $mailing_list_answer) {
    $this->groupMembershipLoader = $group_membership_loader;
    $this->activityActionProcessor = $activity_action_manager;
    $this->panelizer = $panelizer;
    $this->cacheBackend = $cache_backend;
    $this->mailingListAnswer = $mailing_list_answer;

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

    if ($result) {
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

    $mailing_list_group = Group::load($mailing_list_id);

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
      $data['context'] = 'organisation_activity_context';

      $create_action = $this->activityActionProcessor->createInstance('create_activity_action');
      $create_action->create($activity_entity, $data);
    }

    // set moderation state
    $group->set('moderation_state', 'published');
    $group->save();

  }

  /**
   * @param $group_id
   * @return false|int|object
   */
  public function allActivitiesCount($group_id) {
    if($count = $this->cacheBackend->get('dvm_mailing_list:total_activity_count:'.$group_id)) {
      return $count->data;
    } else {
      $group = Group::load($group_id);
      $group_content_questions = count($group->getContent('group_node:question'));
      $group_users = count($group->getMembers([$group->bundle() . '-organisation']));

      $count = (int) $group_content_questions * $group_users;

      $this->cacheBackend->set('dvm_mailing_list:total_activity_count:'.$group->id(), $count);

      return $count;
    }
  }

  /**
   * Check that all activities for mailing list have been created
   * So that we can switch display mode that shows activities
   *
   * @param $group_id
   * @return bool
   */
  public function checkActivitiesCreated($group_id) {
    $all_activities_count = $this->allActivitiesCount($group_id);
    $current_activities_count = (int) $this->mailingListAnswer->getAnswerCount($group_id);
    return $current_activities_count < $all_activities_count ? FALSE : TRUE;
  }

  /**
   * Switch Display Mode.
   *
   * @param $group_id
   * @param string $view_mode
   */
  public function switchDisplay($group_id, $view_mode = 'full') {
    $group = Group::load($group_id);
    $panels_displays = $this->panelizer->getDefaultPanelsDisplays('group', 'mailing_list', $view_mode);
    $this->panelizer->setPanelsDisplay($group, $view_mode, NULL, $panels_displays['default']);
  }

}
