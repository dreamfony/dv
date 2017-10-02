<?php

/**
 * @file
 * Contains \Drupal\activity_logger\Plugin\QueueWorker\MessageQueueBase.
 */

namespace Drupal\dmt_mail\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\collect\Entity\Container;
use Drupal\Core\Url;

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

      // @todo ACTIVITY_STATUS_REJECTED constant does not exist
      // figure out a workflow for this state
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
      // We need to store all data in a uniform way for historical reference.
      // Not relying on any particular third party service like mailgun or gmail for data availability.
      // Save raw message
      // TODO Enable this when collect is fixed
      // https://www.drupal.org/node/2859839
    /*
         $message_id = str_replace(['<', '>'], '', $data['Message-Id']);
         $origin_uri = Url::fromUri('base:webhook/mailgun/message-id/' . $message_id, ['absolute' => TRUE])->toString();
         $origin_uri = str_replace(['%40', '%3D', '%2B'], ['@', '=', '+'], $origin_uri);
         $container = Container::create([
           'origin_uri' => $origin_uri,
           'schema_uri' => 'http://schema.dmtcore.com/webhook/0.0.1/mailgun',
           'type' => 'application/json',
           'data' => json_encode([
             'raw' => $data,
           ]),
         ]);
         $uuid = $container->uuid();
         $container->save();
   */

//    Update activity

      /** @var \Drupal\activity_creator\Entity\Activity $activity */
      // @todo: Inject Entity Type Manager
      $activity = \Drupal::entityTypeManager()
        ->getStorage('activity')
        ->load($data['entity_id']);
      $activity->setModerationState($status);
//    TODO Change this when collect is fixed
//      $activity->revision_log_message = $data['event'].':'. $status . '->' . $uuid;
      $activity->revision_log_message = serialize($data);
      $activity->setNewRevision();
      $activity->save();

    }
  }
}
