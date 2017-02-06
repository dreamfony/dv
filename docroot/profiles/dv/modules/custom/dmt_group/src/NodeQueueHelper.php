<?php

namespace Drupal\dmt_group;

use Drupal\node\Entity\Node;

/**
 * Class NodeQueueHelper.
 *
 * @package Drupal\dmt_group
 */
class NodeQueueHelper {

  /**
   * @param \Drupal\node\Entity\Node $entity
   */
  public function queueNode(Node $entity) {
    // get current node bundle
    $bundle = $entity->bundle();
    if ('issue' === $bundle || 'survey' === $bundle) {

      $moderation_state = $entity->get('moderation_state')->getString();

      // check moderation state
      if ($moderation_state === 'send_email') {

        $data['entity_id'] = $entity->id();
        $queue = \Drupal::queue('dv_organisation_extract_one');

        foreach ($entity->get('field_recipient')
                   ->getValue() as $recipient_group) {
          $data['gid'] = $recipient_group['target_id'];
          $queue->createItem($data);
        }

      }
    }
  }

  public function batchExtractOrganisation(Entity $entity){

  }
}
