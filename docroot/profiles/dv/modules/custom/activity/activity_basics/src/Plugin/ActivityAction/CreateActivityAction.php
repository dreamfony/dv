<?php

/**
 * @file
 * Contains \Drupal\activity_basics\Plugin\ActivityAction\CreateActivityAction.
 */

namespace Drupal\activity_basics\Plugin\ActivityAction;

use Drupal\activity_creator\Plugin\ActivityActionBase;
use Drupal\Core\Entity\Entity;

/**
 * Provides a 'CreateActivityAction' activity action.
 *
 * @ActivityAction(
 *  id = "create_activity_action",
 *  label = @Translation("Action that is triggered when a entity is created"),
 * )
 */
class CreateActivityAction extends ActivityActionBase {

  /**
   * @inheritdoc
   */
  public function create($entity, $data = NULL) {

    if ($this->isValidEntity($entity)) {
        /** @var Entity $entity */
        $data['entity_id'] = $entity->id();
        $data['entity_type_id'] = $entity->getEntityTypeId();
        $queue = \Drupal::queue('activity_logger_message');
        $queue->createItem($data);
    }

  }


}
