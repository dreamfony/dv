<?php

namespace Drupal\dmt_mailing_list\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'Organisation Form Block' block.
 *
 * @Block(
 *   id = "organisation_form_block",
 *   admin_label = @Translation("Organisation form block"),
 * )
 */
class OrganisationFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\dmt_mailing_list\Form\OrganisationForm');
    return $form;
  }

  public function getCacheContexts() {
    //Every new route this block will rebuild
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return \Drupal\Core\Access\AccessResult|\Drupal\Core\Access\AccessResultForbidden
   */
  public function blockAccess(AccountInterface $account) {
    if(\Drupal::routeMatch()->getRouteName() != 'entity.group.canonical') {
      return AccessResult::forbidden();
    }

    $group = \Drupal::routeMatch()->getParameter('group');

    if($group) {
      return AccessResult::allowedIf($group->hasPermission('edit group', $account));
    }

    return AccessResult::forbidden();
  }

}
