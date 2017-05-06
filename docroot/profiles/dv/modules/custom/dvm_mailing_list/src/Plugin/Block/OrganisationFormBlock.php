<?php

namespace Drupal\dvm_mailing_list\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'Organisation Form Block' block.
 *
 * @Block(
 *   id = "organisation_form_block",
 *   admin_label = @Translation("Organisation form block"),
 * )
 */
class OrganisationFormBlock extends BlockBase implements ContextAwarePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\dvm_mailing_list\Form\OrganisationForm');
    return $form;
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param bool $return_as_object
   * @return \Drupal\Core\Access\AccessResult|\Drupal\Core\Access\AccessResultForbidden
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    $group = \Drupal::routeMatch()->getParameter('group');

    if($group) {
      return AccessResult::allowedIf($group->hasPermission('edit group', $account));
    }

    return AccessResult::forbidden();
  }

}
