<?php

/**
 * @file
 * Contains \Drupal\activity_logger\Plugin\QueueWorker\MessageQueueBase.
 */

namespace Drupal\dmt_group\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;

/**
 * A report worker.
 *
 * @QueueWorker(
 *   id = "dv_organisation_extract_two",
 *   title = @Translation("Process dv_organisation_extract_two queue."),
 *   cron = {"time" = 60}
 * )
 *
 */
class DvOrganisationExtractTwo extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $group = Group::load($data['gid']);
    $entity = Node::load($data['entity_id']);
    /// check if node already exists before adding
    if( count( $group->getContentByEntityId('group_node:'. $entity->bundle(), $data['entity_id']) ) === 0 ) {
      $group->addContent($entity, 'group_node:' . $entity->bundle());
    }
  }

}
