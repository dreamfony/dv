<?php

namespace Drupal\dv_positions;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Positions entity.
 *
 * @see \Drupal\dv_positions\Entity\Positions.
 */
class PositionsAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\dv_positions\Entity\PositionsInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished positions entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published positions entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit positions entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete positions entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add positions entities');
  }

}
