<?php

/**
 * @file
 * Contains \Drupal\activity_basics\Plugin\ActivityContext\GroupActivityContext.
 */

namespace Drupal\activity_basics\Plugin\ActivityContext;

use Drupal\activity_creator\Plugin\ActivityContextBase;
use Drupal\comment\CommentInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\dmt_group\GroupHelper;
use Drupal\group\Entity\GroupContent;

/**
 * Provides a 'GroupActivityContext' activity context.
 *
 * @ActivityContext(
 *  id = "group_activity_context",
 *  label = @Translation("Group activity context"),
 * )
 */
class GroupActivityContext extends ActivityContextBase {

  /**
   * {@inheritdoc}
   */
  public function getRecipients(array $data, $last_uid, $limit) {

    $recipients = [];

    return $recipients;
  }

  /**
   * {@inheritdoc}
   */
  public function isValidEntity(ContentEntityInterface $entity) {

    if($entity instanceof ContentEntityInterface) {
      return TRUE;
    }

    return FALSE;
  }

}
