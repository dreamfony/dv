<?php

namespace Drupal\dvm_mailing_list;

use Drupal\group\Entity\GroupContent;
use Drupal\group\Plugin\GroupContentEnabler\GroupMembership;
use Drupal\node\Entity\Node;
use Drupal\group\Entity\Group;


class BatchMailingList {

  public static function addMembers($uids, &$context) {


  }

  public static function getMembership($gids, $group_id, &$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['process'] = 0;

      foreach ($gids as $gid) {
        $gid = $gid['target_id'];
        /** @var Group $group */
        $group = Group::load($gid);
        $membership = $group->getMembers([$group->bundle() . '-organisation']);

        foreach ($membership as $membershipgc) {
          /** @var GroupMembership $membershipgc */
          $org_uid[] = $membershipgc->getGroupContent()->getEntity()->id();
        }
      }
      $context['sandbox']['orgs'] = $org_uid;
      $context['sandbox']['max'] = count($org_uid);
    }
//    process 5 items in one run
    $context['sandbox']['process']+= 3;
    do {
      $org_user = array_pop($context['sandbox']['orgs']);
//      TODO add $org_user to $group_id
      $context['sandbox']['progress']++;
    } while (!empty($context['sandbox']['orgs']) AND ($context['sandbox']['progress'] != $context['sandbox']['process']));


// Check if we should finish
    if (!empty($context['sandbox']['orgs'])) {
      $context['finished'] = 0;
    }
    else {
      $context['finished'] = 1;
    }
  }

  public static function addIssues($nids, &$context) {


  }

  public static function cleanupIssues(&$context) {


  }

  public static function cleanupMembers(&$context) {


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
