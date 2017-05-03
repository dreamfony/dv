<?php

namespace Drupal\dvm_mailing_list;

use Drupal\activity_creator\Plugin\Type\ActivityActionManager;
use Drupal\activity_moderation\Plugin\Type\ActivityModerationManager;
use Drupal\Core\Entity\ContentEntityInterface;
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
use Symfony\Component\HttpFoundation\RedirectResponse;


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
   * @param \Drupal\panelizer\PanelizerInterface $panelizer
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\dvm_mailing_list\MailingListAnswer $mailing_list_answer
   */
  public function __construct(PanelizerInterface $panelizer, CacheBackendInterface $cache_backend, MailingListAnswer $mailing_list_answer) {
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
    $results = $query->execute();

    foreach ($results as $result) {
      /** @var \Drupal\group\Entity\GroupInterface $group */
      $group = Group::load($result);

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
   * @param $group_id
   * @return false|int|object
   */
  public function allActivitiesCount($group_id) {
    if ($count = $this->cacheBackend->get('dvm_mailing_list:total_activity_count:' . $group_id)) {
      return $count->data;
    }
    else {
      $group = Group::load($group_id);
      $group_content_questions = count($group->getContent('group_node:question'));
      $group_users = count($group->getMembers([$group->bundle() . '-organisation']));

      $count = (int) $group_content_questions * $group_users;

      $this->cacheBackend->set('dvm_mailing_list:total_activity_count:' . $group->id(), $count);

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
