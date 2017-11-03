<?php

namespace Drupal\dmt_mail\EventSubscriber;

use Drupal\webhooks\Event\ReceiveEvent;
use Drupal\webhooks\Webhook;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\mailgun\DrupalMailgun;

class MailgunWebhookEvent implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    $events['webhook.receive'][] = ['receive'];
    return $events;
  }

  public function receive(ReceiveEvent $receive) {

    /** @var Webhook $webhook */
    $webhook = $receive->getWebhook();
    $payload = static::decode($webhook->getPayload());

    // TODO do we need to check which webhook is being used
    // this code seems to be working for every webhook defined by the system
    // could possibly pass junk to the queue

    $drupalMailgun = new DrupalMailgun();
    $isverified = $drupalMailgun->verifyWebhookSignature($payload);

    if ($isverified){
      unset($payload['message-headers']);
      $queue = \Drupal::queue('dmt_mailgun');
      $queue->createItem($payload);
    }
  }


  /**
   * Until we have better solution to decode
   * https://www.drupal.org/files/issues/return-data-2851615-2.patch
   */
  protected static function decode($data) {
    parse_str($data, $output);
    return $output;
  }

}
