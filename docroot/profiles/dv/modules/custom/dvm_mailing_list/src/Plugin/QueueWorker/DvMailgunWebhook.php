<?php

/**
 * @file
 * Contains \Drupal\activity_logger\Plugin\QueueWorker\MessageQueueBase.
 */

namespace Drupal\dvm_mailing_list\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;


/**
 * A report worker.
 *
 * @QueueWorker(
 *   id = "dv_mailgun_webhook",
 *   title = @Translation("Process dv_mailgun_webhook queue."),
 *   cron = {"time" = 60}
 * )
 *
 */
class DvMailgunWebhook extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {

    /**
     * bounce, deliver, drop, spam, unsubscribe, click, open
     */

    switch ($data['event']) {
      case 'deliver':
        $status = ACTIVITY_STATUS_SENT;
        break;
      case 'drop':
        $status = ACTIVITY_STATUS_DELIVERY_ERROR;
        break;

      case 'spam':
        $status = ACTIVITY_STATUS_REJECTED;
        break;

      case 'unsubscribe':
        $status = ACTIVITY_STATUS_REJECTED;
        break;

      case 'click':
        $status = ACTIVITY_STATUS_SEEN;
        break;

      case 'opened':
        $status = ACTIVITY_STATUS_SEEN;
        break;

      default:
        $status = ACTIVITY_STATUS_DELIVERY_ERROR;
        break;

    }

    if (isset($data['entity_id'])) {
      $activity = \Drupal::entityManager()
        ->getStorage('activity')
        ->load($data['entity_id']);
      $activity->set('field_activity_status', $status);
      $activity->revision_log = $data;
      $activity->save();

    }

  }
}
