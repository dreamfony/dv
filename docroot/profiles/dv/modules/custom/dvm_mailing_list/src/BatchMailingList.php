<?php

namespace Drupal\dvm_mailing_list;

use Drupal\group\Entity\GroupContent;
use Drupal\group\Plugin\GroupContentEnabler\GroupMembership;
use Drupal\group\Entity\Group;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;


class BatchMailingList {

  /**
   * Clean all content in the group. Except group owner.
   *
   * @param $group_id
   * @param $context
   */
  public static function cleanGroup($group_id, &$context) {
    /** @var Group $group */
    $group = Group::load($group_id);
    $group_owner = $group->getOwner();
    $group_content = $group->getContent();
    foreach ($group_content as $gc) {
      /** @var GroupContent $gc */
      // check if user is admin in the group if not delete
      if($gc->getEntity() !== $group_owner) {
        $gc->delete();
      }
    }
  }

  /**
   * @param $gids
   * @param $group_id
   * @param $context
   */
  public static function addMembers($gids, $group_id, &$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['process'] = 0;

      foreach ($gids as $gid) {
        $gid = $gid['target_id'];

        if ($gid) {
          /** @var Group $group */
          $group = Group::load($gid);
          $membership = $group->getMembers([$group->bundle() . '-organisation']);

          foreach ($membership as $membershipgc) {
            /** @var GroupMembership $membershipgc */
            $org_uid[] = $membershipgc->getGroupContent()->getEntity()->id();
          }
        }
      }
      $context['sandbox']['orgs'] = $org_uid;
      $context['sandbox']['max'] = count($org_uid);
    }
//    process 5 items in one run
    $context['sandbox']['process'] += 3;

    $group = Group::load($group_id);

    do {
      $org_user_uid = array_pop($context['sandbox']['orgs']);
      $org_user = User::load($org_user_uid);
      $group->addMember($org_user, ['group_roles' => [$group->bundle() . '-organisation']]);

      $context['sandbox']['progress']++;
    } while (!empty($context['sandbox']['orgs']) && ($context['sandbox']['progress'] != $context['sandbox']['process']));

  }

  /**
   * @param $nids
   * @param $group_id
   * @param $context
   */
  public static function addIssues($nids, $group_id, &$context) {

    /// TODO make batch work
    $group = Group::load($group_id);

    foreach ($nids as $nid) {
      $nid = $nid['target_id'];
      /** @var Group $group */
      $node = Node::load($nid);
      $group->addContent($node, 'group_node:issue');
    }

    $context['finished'] = 1;
  }

  public static function deleteNodeExampleFinishedCallback($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One post processed.', '@count posts processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    drupal_set_message($message);
  }


}
