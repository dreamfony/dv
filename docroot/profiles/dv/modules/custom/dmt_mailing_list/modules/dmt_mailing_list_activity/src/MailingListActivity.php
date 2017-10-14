<?php

namespace Drupal\dmt_mailing_list_activity;

use Drupal\group\Entity\Group;
use Drupal\Core\Cache\CacheBackendInterface;


/**
 * Class MailingListActivity
 *
 * @package Drupal\dmt_mailing_list_activity
 */
class MailingListActivity {

  /**
   * @var CacheBackendInterface
   *   Cache backend.
   */
  protected $cacheBackend;

  /**
   * MailingList constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   */
  public function __construct(CacheBackendInterface $cache_backend) {
    $this->cacheBackend = $cache_backend;
  }

  /**
   * @param $group_id
   * @return false|int|object
   */
  public function allActivitiesCount($group_id) {
    if ($count = $this->cacheBackend->get('dmt_mailing_list:total_activity_count:' . $group_id)) {
      return $count->data;
    }
    else {
      $group = Group::load($group_id);
      $group_content_contents = count($group->getContent('group_node:content'));
      $group_users = count($group->getMembers([$group->bundle() . '-organisation']));
      $count = (int) $group_content_contents * $group_users;
      $this->cacheBackend->set('dmt_mailing_list:total_activity_count:' . $group->id(), $count);
      return $count;
    }
  }

  /**
   * Check that all activities for mailing list have been created
   *
   * @param $group_id
   * @return bool
   */
  public function checkActivitiesCreated($group_id) {
    $all_activities_count = $this->allActivitiesCount($group_id);
    $current_activities_count = (int) $this->getAnswerCount($group_id);
    return $current_activities_count < $all_activities_count ? FALSE : TRUE;
  }

  /**
   * Get answer count.
   *
   * @param $group_id
   * @param $user_id
   * @param bool $status
   * @return array|int
   */
  public function getAnswerCount($group_id, $user_id = FALSE, $status = FALSE) {
    $query = \Drupal::entityQuery('activity')
      ->condition('field_activity_mailing_list.target_id', $group_id);

    if($user_id) {
      $query->condition('field_activity_recipient_user.target_id', $user_id);
    }

    if ($status) {
      /** @see activity_creator_query_cm_states_alter */
      $query->addTag('cm_states');
      $query->addMetaData('states', [$status]);
    }

    $count = $query->count()->execute();

    return $count;
  }

}
