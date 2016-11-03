<?php

namespace Drupal\user_groups\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;

/**
 * Class GeoAreaNode
 * @package Drupal\geo_area_group
 */
class UserGroupIssue implements EventSubscriberInterface {

  /**
   * On Issue Insert.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent $event
   */
  public function nodeInsert(EntityInsertEvent $event) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $event->getEntity();
    // check if entity is node
    if ($entity->getEntityTypeId() == 'node') {
      // check bundle
      if ('issue' === $entity->bundle()) {
          \Drupal::service('user_group.user_group')->groupUserIssue($entity, 'insert');
      }
    }
  }

  /**
   * On Issue Update.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   */
  public function nodeUpdate(EntityUpdateEvent $event) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $event->getEntity();
    // check if entity is node
    if ($entity->getEntityTypeId() == 'node') {
      // check bundle
      if ('issue' === $entity->bundle()) {
        \Drupal::service('user_group.user_group')->groupUserIssue($entity, 'update');
      }
    }
  }

  /**
   * @inheritdoc
   */
  static function getSubscribedEvents() {
    return [
      HookEventDispatcherEvents::ENTITY_INSERT => [
        ['nodeInsert']
      ],
      HookEventDispatcherEvents::ENTITY_UPDATE => [
        ['nodeUpdate']
      ],
    ];
  }

}