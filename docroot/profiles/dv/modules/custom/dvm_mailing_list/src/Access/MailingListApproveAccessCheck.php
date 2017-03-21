<?php

namespace Drupal\dvm_mailing_list\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupTypeInterface;
use Symfony\Component\Routing\Route;


/**
 * Class MailingListApproveAccessCheck
 * @package Drupal\dvm_mailing_list\Access
 */
class MailingListApproveAccessCheck implements AccessInterface {

  /**
   * Checks access to the approve link.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group to create the subgroup in.
   * @param \Drupal\group\Entity\GroupTypeInterface $group_type
   *   The type of subgroup to create in the group.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, AccountInterface $account, GroupInterface $group) {
    $needs_access = $route->getRequirement('_mailing_list_approve_access') === 'TRUE';

    // if group is not mailing list we don't show this link
    if($group->bundle() != 'mailing_list') {
      return AccessResult::forbidden();
    }

    // check moderation state
    $moderation_state = $group->moderation_state->value === 'email';

    // Determine whether the user can create groups of the provided type.
    $access = $group->hasPermission('approve sending', $account);

    return AccessResult::allowedIf(($access and $moderation_state) xor !$needs_access);
  }

}
