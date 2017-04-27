<?php

/**
 * @file
 * Contains \Drupal\activity_basics\Plugin\ActivityAction\CreateActivityAction.
 */

namespace Drupal\activity_moderation\Plugin\ActivityAction;

use Drupal\activity_creator\Plugin\ActivityActionBase;
use Drupal\Core\Entity\Entity;

/**
 * Provides a 'ModerationAction' activity action.
 *
 * @ActivityAction(
 *  id = "moderation_action",
 *  label = @Translation("Action that is triggered when Moderation Ticket should be created"),
 * )
 */
class ModerationAction extends ActivityActionBase {

  /**
   * @inheritdoc
   */
  public function create($entity, $data = []) {

    if ($this->isValidEntity($entity)) {
        /** @var Entity $entity */
        $data = $data +
          [
          'action' => 'moderation_action',
          'entity_id' => $entity->id(),
          'entity_type_id' => $entity->getEntityTypeId()
          ];

        $queue = \Drupal::queue('activity_logger_message');
        $queue->createItem($data);
    }

  }


}
