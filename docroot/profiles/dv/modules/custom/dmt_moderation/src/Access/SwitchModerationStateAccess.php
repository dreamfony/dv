<?php

namespace Drupal\dmt_moderation\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\dmt_moderation\Plugin\Type\SwitchModerationStateManager;
use Symfony\Component\Routing\Route;


/**
 * Class SwitchModerationStateAccess
 * @package Drupal\dmt_moderation\Access
 */
class SwitchModerationStateAccess implements AccessInterface {

  /**
   * @var \Drupal\dmt_moderation\Plugin\Type\SwitchModerationStateManager
   */
  protected $switchModerationStateManager;


  public function __construct(SwitchModerationStateManager $switchModerationStateManager) {
    $this->switchModerationStateManager = $switchModerationStateManager;
  }

  /**
   * Checks access for switch moderation state link.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param ContentEntityInterface $entity
   *   Content Entity
   * @param string $state_id
   *   State Id
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, AccountInterface $account, ContentEntityInterface $entity, $state_id) {
    $plugin_id = $this->switchModerationStateManager->getPluginIdByEntity($entity);
    /** @var \Drupal\dmt_moderation\SwitchModerationStateBase $sms */
    $sms = $this->switchModerationStateManager->createInstance($plugin_id);
    $old_state = $entity->moderation_state->value;
    $access = $sms->isValidTransition($entity, $account, $old_state, $state_id);

    return AccessResult::allowedIf( $access );
  }

}
