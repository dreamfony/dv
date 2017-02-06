<?php

namespace Drupal\dvm_mailing_list;

use Drupal\node\Entity\Node;
use Drupal\group\Entity\Group;


class BatchMailingList {

  public static function addMembers($uids, &$context) {


  }

  public static function getMembership($gids, &$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_node'] = 0;

      foreach ($gids as $gid) {

        $gid = $gid['target_id'];
        /** @var Group $group */
        $group = Group::load($gid['target_id']);

        switch ($group->bundle()) {
          case 'organisation':
            $data['gid'][] = $gid;
            break;
          default:
            $gids = BatchMailingList::findOrganisationGroupsFromOtherGroupTypes($gid);
            foreach ($gids as $gid) {
              $data['gid'][] = $gid;
            }
        }
      }
      $context['sandbox']['max'] = count($data['gid']);
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

  public static function findOrganisationGroupsFromOtherGroupTypes($gid) {
    $group = Group::load($gid);
    $organisation_group_contents = $group->getContent('group_node:organisation');

    foreach ($organisation_group_contents as $organisation_group_content) {
      /** @var GroupContent $organisation_group_content */
      $organisation_node = $organisation_group_content->getEntity();
      if ($groupContents = GroupContent::loadByEntity($organisation_node)) {
        // Potentially there are more than one.
        foreach ($groupContents as $groupContent) {
          /** @var GroupContent $groupContent */
          // Set the group id.
          /** @var Group $group */
          $group = $groupContent->getGroup();
          $group_type = $group->bundle();
          if ($group_type === 'organisation') {
            $gids[] = $group->id();
          }
        }

      }
    }


    return $gids;
  }

}
