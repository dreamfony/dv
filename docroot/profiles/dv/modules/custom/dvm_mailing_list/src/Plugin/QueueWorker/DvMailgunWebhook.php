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
      case 'bounce':
      case 'drop':
// TODO what to do here
        break;
      default:
        $data['entity_id'];
        $data['hash'];
// TODO save this in activity
    }

  }
}
