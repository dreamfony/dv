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
     * accepted    Mailgun accepted the request to send/forward the email and the message has been placed in queue.
     * rejected    Mailgun rejected the request to send/forward the email.
     * delivered    Mailgun sent the email and it was accepted by the recipient email server.
     * failed    Mailgun could not deliver the email to the recipient email server.
     * opened    The email recipient opened the email and enabled image viewing. Open tracking must be enabled in the Mailgun control panel, and the CNAME record must be pointing to mailgun.org.
     * clicked    The email recipient clicked on a link in the email. Click tracking must be enabled in the Mailgun control panel, and the CNAME record must be pointing to mailgun.org.
     * unsubscribed    The email recipient clicked on the unsubscribe link. Unsubscribe tracking must be enabled in the Mailgun control panel.
     * complained    The email recipient clicked on the spam complaint button within their email client. Feedback loops enable the notification to be received by Mailgun.
     * stored    Mailgun has stored an incoming message
     */

    switch ($data['event']) {
      case 'delivered':
        $status = ACTIVITY_STATUS_SENT;
        break;
      case 'failed':
      case 'rejected':
        $status = ACTIVITY_STATUS_DELIVERY_ERROR;
        break;

      case 'complained':
        $status = ACTIVITY_STATUS_REJECTED;
        break;

      case 'unsubscribed':
        $status = ACTIVITY_STATUS_REJECTED;
        break;

      case 'clicked':
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
      $activity->revision_log = serialize($data);
      $activity->setNewRevision();
      $activity->save();

    }

  }
}
