<?php

/**
 * @file
 * Contains \Drupal\activity_basics\Plugin\ActivityContext\CommunityActivityContext.
 */

namespace Drupal\dmt_group_comments\Plugin\ActivityContext;

use Drupal\activity_creator\ActivityFactory;
use Drupal\activity_creator\Plugin\ActivityContextBase;
use Drupal\comment\CommentInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\group\Entity\Group;
use Drupal\group\GroupMembership;

/**
 * Provides a 'Group Comments Context' activity context.
 *
 * @ActivityContext(
 *  id = "group_comments_context",
 *  label = @Translation("Group Comments Context"),
 * )
 */
class GroupCommentContext extends ActivityContextBase {

  /**
   * {@inheritdoc}
   */
  public function getRecipients(array $data, $last_uid, $limit) {

    $recipients = [];

    // We only know the context if there is a related object.
    if (isset($data['related_object']) && !empty($data['related_object'])) {

      /** @var Group $group */
      $group = ActivityFactory::getCommentedEntity($data);

      /// @todo: most probably add all commentators to a group so we can send them notifications
      $memberships = $group->getMembers([$group->bundle() . '-owner']);

      foreach ($memberships as $membership) {
        /** @var GroupMembership $membership */
        $recipients[] = [
          'target_type' => 'user',
          'target_id' => $membership->getUser()->id(),
        ];
      }
    }
    return $recipients;
  }

  /**
   * Check if entity is a valid activity Entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return bool
   */
  public function isValidEntity(ContentEntityInterface $entity) {
    // Special cases for comments.
    if ($entity->getEntityTypeId() === 'comment') {
      // Returns the entity to which the comment is attached.
      /** @var CommentInterface $entity */
      $entity = $entity->getCommentedEntity();
    }

    if (!isset($entity)) {
      return FALSE;
    }

    return TRUE;
  }

}
