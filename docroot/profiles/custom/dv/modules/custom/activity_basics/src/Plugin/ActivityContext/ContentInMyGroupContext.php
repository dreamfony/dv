<?php

/**
 * @file
 * Contains \Drupal\activity_basics\Plugin\ActivityContext\ContentInMyGroupActivityContext.
 */

namespace Drupal\activity_basics\Plugin\ActivityContext;

use Drupal\activity_creator\Plugin\ActivityContextBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;
use Drupal\group\GroupMembership;
use Drupal\activity_creator\ActivityFactory;
use Drupal\dmt_group\GroupHelper;
use Drupal\group\GroupMembershipLoaderInterface;

/**
 * Provides a 'ContentInMyGroupActivityContext' acitivy context.
 *
 * @ActivityContext(
 *  id = "content_in_my_group_activity_context",
 *  label = @Translation("Content in my group activity context"),
 * )
 */
class ContentInMyGroupActivityContext extends ActivityContextBase {

  /**
   * {@inheritdoc}
   */
  public function getRecipients(array $data, $last_uid, $limit) {
    $recipients = [];

    // We only know the context if there is a related object.
    if (isset($data['related_object']) && !empty($data['related_object'])) {

      $referenced_entity = ActivityFactory::getActivityRelatedEntity($data);

      if ($gid = GroupHelper::getGroupFromEntity($referenced_entity)) {
        $recipients[] = [
          'target_type' => 'group',
          'target_id' => $gid,
        ];
        $group = Group::load($gid);

        /** @var GroupMembershipLoaderInterface $memberships */
        $memberships = \Drupal::service('group.membership_loader');
        $memberships->loadByGroup($group);

        foreach ($memberships as $membership) {
          /** @var GroupMembership $membership */
          $recipients[] = [
            'target_type' => 'user',
            'target_id' => $membership->getUser()->id(),
          ];
        }
      }
    }

    return $recipients;
  }

  /**
   * {@inheritdoc}
   */
  public function isValidEntity(ContentEntityInterface $entity) {
    // Check if it's placed in a group (regardless off content type).
    if ($group_entity = GroupContent::loadByEntity($entity)) {
      return TRUE;
    }
    /*
    if ($entity->getEntityTypeId() === 'post') {
      if (!empty($entity->get('field_recipient_group')->getValue())) {
        return TRUE;
      }
    }
    */
    return FALSE;
  }

}
