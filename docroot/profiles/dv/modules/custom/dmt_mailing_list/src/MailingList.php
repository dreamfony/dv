<?php

namespace Drupal\dmt_mailing_list;

use Drupal\group\Entity\Group;
use Drupal\group\GroupMembership;
use Drupal\panelizer\PanelizerInterface;
use Drupal\user\Entity\User;


/**
 * Class Mailing List
 * @package Drupal\dmt_mailing_list
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
   * @var \Drupal\dmt_mailing_list\MailingListAnswer
   */
  protected $mailingListAnswer;


  /**
   * MailingList constructor.
   *
   * @param \Drupal\panelizer\PanelizerInterface $panelizer
   * @param \Drupal\dmt_mailing_list\MailingListAnswer $mailing_list_answer
   */
  public function __construct(PanelizerInterface $panelizer, MailingListAnswer $mailing_list_answer) {
    $this->panelizer = $panelizer;
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

    // check if user already has an empty survey
    $emptyGroup = $this->getUsersEmptyGroup();

    if($emptyGroup) {
      return $emptyGroup->id();
    }

    // create a new empty survey
    $group = Group::create([
      'label' => $this->mailingListLabel,
      'type' => $this->mailingListType
    ]);

    $group->save();

    return $group->id();
  }

  /**
   * Check if user already has empty Survey
   *
   * @return bool|\Drupal\group\Entity\GroupInterface
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

      $group_content = $group->getContent('group_node:content');
      $group_users = $group->getMembers([$this->mailingListType . '-organisation']);
      if (empty($group_content) && empty($group_users)) {
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
   * Switch Display Mode.
   *
   * @param $group_id
   * @param string $view_mode
   * @param string $panels_display
   */
  public function switchDisplay($group_id, $view_mode = 'full', $panels_display = 'default') {
    $group = Group::load($group_id);
    $panels_displays = $this->panelizer->getDefaultPanelsDisplays('group', 'mailing_list', $view_mode);
    $this->panelizer->setPanelsDisplay($group, $view_mode, NULL, $panels_displays[$panels_display]);
  }

}
