<?php

namespace Drupal\dvm_mailing_list\Plugin\Block;

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
    $url = Url::fromRoute('dvm_mailing_list.edit_title', [
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
   * @param bool $return_as_object
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    // if route is not group view return forbidden
    if(\Drupal::routeMatch()->getRouteName() != 'entity.group.canonical') {
      return AccessResult::forbidden();
    }

    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $this->getContextValue('group');

    if ($group->id() && $group->bundle() == 'mailing_list' && $group->access('update')) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
