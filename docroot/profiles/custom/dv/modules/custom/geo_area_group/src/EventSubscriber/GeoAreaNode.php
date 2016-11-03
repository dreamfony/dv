<?php

namespace Drupal\geo_area_group\EventSubscriber;

use Drupal\dmt_group\AddGroupToSubgroup;
use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class GeoAreaNode
 * @package Drupal\geo_area_group
 */
class GeoAreaNode implements EventSubscriberInterface {

  /** @var \Drupal\dmt_group\AddGroupToSubgroup */
  private $addGeoArea;

  /**
   * GeoAreaNode constructor.
   * @param \Drupal\dmt_group\AddGroupToSubgroup $add_geo_area
   */
  public function __construct(AddGroupToSubgroup $add_geo_area) {
    $this->addGeoArea = $add_geo_area;
  }

  /**
   * On Location node insert create a Location group, and
   * add a Location node to that group.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent $event
   */
  public function nodeInsert(EntityInsertEvent $event) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() == 'node') {

      // get current node bundle
      $bundle = $entity->bundle();

      // check if current node is geo_area node
      if ('geo_area' === $bundle) {
        $this->addGeoArea->add($entity, 'geo_area_group', 'field_geo_area_parent');
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
    ];
  }

}