<?php

namespace Drupal\dvm_mailing_list\Plugin\views\field;

use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\Group;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to delete group content.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("group_content_mailing_list_stats")
 */
class GroupContentMailingListStats extends FieldPluginBase {

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * Get count.
   *
   * @param $group_id
   * @param $user_id
   * @param bool $status
   * @return array|int
   */
  public function getCountByStatus($group_id, $user_id, $status = FALSE) {

    // Total questions
    $query = \Drupal::entityQuery('activity')
      ->condition('field_activity_mailing_list.target_id', $group_id)
      ->condition('field_activity_recipient_user.target_id', $user_id);

      if($status) {
        $query->condition('field_activity_status', $status);
      }

    $count = $query->count()
      ->execute();

    return $count;
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var GroupContent $group_content */
    $group_content = $values->_entity;
    /** @var User $user */
    $user_id = $values->users_field_data_group_content_field_data_uid;
    /** @var Group $group */
    $group_id = $group_content->getGroup()->id();

    $total = $this->getCountByStatus($group_id, $user_id);
    $answered = $this->getCountByStatus($group_id, $user_id, ACTIVITY_STATUS_ANSWERED);

    return $answered. '/' . $total;
  }
}
