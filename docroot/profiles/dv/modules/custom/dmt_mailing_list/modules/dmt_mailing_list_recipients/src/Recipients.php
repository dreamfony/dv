<?php

namespace Drupal\dmt_mailing_list_recipients;

use Drupal\group\Entity\Group;
use Drupal\group\GroupMembership;
use Drupal\user\Entity\User;


class Recipients {

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

}
