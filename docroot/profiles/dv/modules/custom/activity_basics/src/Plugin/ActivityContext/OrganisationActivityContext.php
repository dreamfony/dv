<?php

/**
 * @file
 * Contains \Drupal\activity_basics\Plugin\ActivityContext\OrganisationActivityContext.
 */

namespace Drupal\activity_basics\Plugin\ActivityContext;

use Drupal\activity_creator\Plugin\ActivityContextBase;
use Drupal\group\Entity\Group;
use Drupal\group\GroupMembership;


/**
 * Provides a 'OrganisationActivityContext' activity context.
 *
 * @ActivityContext(
 *  id = "organisation_activity_context",
 *  label = @Translation("Organisation activity context"),
 * )
 */
class OrganisationActivityContext extends ActivityContextBase {

  /**
   * {@inheritdoc}
   */
  public function getRecipients(array $data, $last_uid, $limit) {
    $recipients = [];

    // We only know the context if there is a related object.
    if (isset($data['related_object']) && !empty($data['related_object'])) {

      $gid = $data['group_id'];
      $group = Group::load($gid);

      $memberships = $group->getMembers([$group->bundle() . '-organisation']);

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

}
