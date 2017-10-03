<?php
namespace Drupal\dmt_mailing_list\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'Content form block' block.
 *
 * @Block(
 *   id = "content_form_block",
 *   admin_label = @Translation("Content form block"),
 * )
 */
class ContentFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $values = array('type' => 'content');

    $node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->create($values);

    $form = \Drupal::entityTypeManager()
      ->getFormObject('node', 'default')
      ->setEntity($node);

    $form = \Drupal::formBuilder()->getForm($form);

    return $form;
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
