<?php

namespace Drupal\organisation_group\EventSubscriber;

use Drupal\dmt_group\AddGroupToSubgroup;
use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrganisationNode
 * @package Drupal\organisation_group
 */
class OrganisationNode implements EventSubscriberInterface {

  /** @var \Drupal\dmt_group\AddGroupToSubgroup */
  private $addOrganisation;

  /**
   * OrganisationNode constructor.
   * @param \Drupal\dmt_group\AddGroupToSubgroup $add_organisation
   */
  public function __construct(AddGroupToSubgroup $add_organisation) {
    $this->addOrganisation = $add_organisation;
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

      // check if current node is organisation node
      if ('organisation' === $bundle) {
        $this->addOrganisation->add($entity, 'organisation', 'field_o_parent_organisation');
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