<?php

/**
 * @file
 * Contains \Drupal\activity_basics\Plugin\ActivityEntityCondition\CreateActivityEntityCondition.
 */

namespace Drupal\activity_basics\Plugin\ActivityEntityCondition;

use Drupal\activity_creator\Plugin\ActivityEntityConditionBase;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides a 'CommentReply' activity condition.
 *
 * @ActivityEntityCondition(
 *  id = "comment_all",
 *  label = @Translation("All comments"),
 *  entities = {"comment" = {}}
 * )
 */
class CommentAllActivityEntityCondition extends ActivityEntityConditionBase {

  /**
   * {@inheritdoc}
   */
  public function isValidEntityCondition(ContentEntityInterface $entity) {
    if ($entity->getEntityTypeId() === 'comment') {
      return TRUE;
    }
    return FALSE;
  }

}
