<?php

/**
 * @file
 * Contains \Drupal\activity_logger\Plugin\QueueWorker\MessageQueueBase.
 */

namespace Drupal\dmt_group\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;


/**
 * A report worker.
 *
 * @QueueWorker(
 *   id = "dv_organisation_extract_one",
 *   title = @Translation("Process dv_organisation_extract_one queue."),
 *   cron = {"time" = 60}
 * )
 *
 */
class DvOrganisationExtractOne extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {

    $gid = $data['gid'];
    $data2['entity_id'] = $data['entity_id'];

    $queue = \Drupal::queue('dv_organisation_extract_two');

    /** @var Group $group */
    $group = Group::load($gid);

    switch ($group->bundle()) {
      case 'organisation':
        $data2['gid'] = $gid;
        $queue->createItem($data);
        break;
      default:
        $gids = $this->findOrganisationGroupsFromOtherGroupTypes($gid);
        foreach ($gids as $ogid) {
          $data2['gid'] = $ogid;
          $queue->createItem($data2);
        }
    }

  }

  protected function findOrganisationGroupsFromOtherGroupTypes($gid) {
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
