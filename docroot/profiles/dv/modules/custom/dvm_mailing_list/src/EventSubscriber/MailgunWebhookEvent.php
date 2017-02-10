<?php

namespace Drupal\dvm_mailing_list\EventSubscriber;

use Drupal\webhooks\Event\ReceiveEvent;
use Drupal\webhooks\Webhook;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mailgun\Mailgun;

class MailgunWebhookEvent implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    $events['webhook.receive'][] = ['receive'];
    return $events;
  }

  public function receive(ReceiveEvent $receive) {

    /** @var Webhook $webhook */
    $webhook = $receive->getWebhook();

    $payload = $webhook->getPayload();
    $headers = $webhook->getHeaders();


  }
}