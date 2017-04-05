<?php
namespace Drupal\dvm_mailing_list\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;

/**
 * Provides a 'Demo' block.
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
    $form = \Drupal::formBuilder()->getForm('Drupal\dvm_mailing_list\Form\OrganisationForm');

    return $form;
  }

  public function access(AccountInterface $account, $return_as_object = FALSE) {
    $group = \Drupal::routeMatch()->getParameter('group');
    return $group->hasPermission('edit group', $account);
  }

}
