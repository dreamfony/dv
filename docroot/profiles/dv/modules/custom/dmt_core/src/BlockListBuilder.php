<?php

namespace Drupal\dmt_core;

use Drupal\Component\Utility\Html;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\block\BlockListBuilder as BlockListBuilderCore;

/**
 * Defines a class to build a listing of block entities.
 *
 * @see \Drupal\block\Entity\Block
 */
class BlockListBuilder extends BlockListBuilderCore {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_ids = $this->getEntityIds();

    /** @var \Drupal\domain\Entity\Domain $active */
    $active = \Drupal::service('domain.negotiator')->getActiveDomain();
    if (!$active->isDefault()) {
      $entities = $this->storage->loadMultiple($entity_ids);
    } else {
      $entities = $this->storage->loadMultipleOverrideFree($entity_ids);
    }

    // Sort the entities using the entity class's sort() method.
    // See \Drupal\Core\Config\Entity\ConfigEntityBase::sort().
    uasort($entities, array($this->entityType->getClass(), 'sort'));
    return $entities;
  }

}
