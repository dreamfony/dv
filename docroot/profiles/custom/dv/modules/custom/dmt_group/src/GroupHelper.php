<?php

namespace Drupal\dmt_group;

use Drupal\comment\CommentInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\group\Entity\GroupContent;
use Drupal\node\Entity\Node;

/**
 * Class SocialGroupHelperService.
 *
 * @package Drupal\social_group
 */
class GroupHelper {

  /**
   * Returns a group id from a entity (post, node).
   *
   * @param $referenced_entity
   * @return null
   */
  public static function getGroupFromEntity($referenced_entity) {
    $gid = NULL;

    // Special cases for comments.
    // Returns the entity to which the comment is attached.
    if ($referenced_entity['target_type'] === 'comment') {
      /** @var CommentInterface $comment */
      $comment = \Drupal::entityTypeManager()
        ->getStorage('comment')
        ->load($referenced_entity['target_id']);
      $commented_entity = $comment->getCommentedEntity();
      $referenced_entity['target_type'] = $commented_entity->getEntityTypeId();
      $referenced_entity['target_id'] = $commented_entity->id();
    }

    if ($referenced_entity['target_type'] === 'node') {
      // Try to load the entity.
      if ($node = Node::load($referenced_entity['target_id'])) {
        // Try to load group content from entity.
        if ($groupContent = GroupContent::loadByEntity($node)) {
          // Potentially there are more than one.
          $groupContent = reset($groupContent);
          // Set the group id.
          $gid = $groupContent->getGroup()->id();
        }
      }
    }
    return $gid;
  }

}
