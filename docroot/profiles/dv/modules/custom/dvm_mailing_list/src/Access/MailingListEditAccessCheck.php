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
class MailingListEditAccessCheck implements AccessInterface {

  /**
   * Checks access to the approve link.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group to create the subgroup in.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, AccountInterface $account, GroupInterface $group) {

    if($group->bundle() != 'mailing_list') {
      return AccessResult::forbidden();
    }

    return AccessResult::neutral();
  }

}
