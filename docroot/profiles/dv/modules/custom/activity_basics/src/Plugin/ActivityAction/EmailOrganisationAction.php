<?php

/**
 * @file
 * Contains \Drupal\activity_basics\Plugin\ActivityAction\EmailOrganisationAction.
 */

namespace Drupal\activity_basics\Plugin\ActivityAction;

use Drupal\activity_creator\Plugin\ActivityActionBase;
use Drupal\Core\Entity\Entity;

/**
 * Provides a 'EmailOrganisationAction' activity action.
 *
 * @ActivityAction(
 *  id = "email_organisation_action",
 *  label = @Translation("Action that is triggered Email should be sent to Organisation"),
 * )
 */
class EmailOrganisationAction extends ActivityActionBase {

  /**
   * @inheritdoc
   */
  public function create($entity) {

    if ($this->isValidEntity($entity)) {
        /** @var Entity $entity */
        $data['entity_id'] = $entity->id();
        $data['entity_type_id'] = $entity->getEntityTypeId();
        $data['action'] = 'email_organisation_action';
        $queue = \Drupal::queue('activity_logger_message');
        $queue->createItem($data);
    }

  }


}
