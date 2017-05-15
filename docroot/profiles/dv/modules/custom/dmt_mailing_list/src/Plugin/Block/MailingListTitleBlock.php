<?php

namespace Drupal\dmt_mailing_list\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'Demo' block.
 *
 * @Block(
 *   id = "mailing_list_title_block",
 *   admin_label = @Translation("Mailing List Title block"),
 *   context = {
 *     "group" = @ContextDefinition("entity:group", required = FALSE)
 *   }
 * )
 */
class MailingListTitleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $link = $this->editTitleLink();
    $content['block_content'] = $link;
    return $content;
  }

  /**
   * Edit title link.
   *
   * @return array
   */
  public function editTitleLink() {
    $url = Url::fromRoute('dmt_mailing_list.edit_title', [
      'group' => $this->getContextValue('group')->id()
    ]);
    $link = Link::fromTextAndUrl(t('Edit Title'), $url);
    $link = $link->toRenderable();
    // If you need some attributes.
    $link['#attributes'] = array('class' => array('use-ajax'));
    return $link;
  }


  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return \Drupal\Core\Access\AccessResult|\Drupal\Core\Access\AccessResultForbidden
   */
  public function blockAccess(AccountInterface $account) {
    // if route is not group view return forbidden
    if(\Drupal::routeMatch()->getRouteName() != 'entity.group.canonical') {
      return AccessResult::forbidden();
    }

    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $this->getContextValue('group');

    return AccessResult::allowedIf($group->id() && $group->bundle() == 'mailing_list' && $group->access('update'));
  }

}
