<?php

namespace Drupal\dvm_mailing_list\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'Demo' block.
 *
 * @Block(
 *   id = "mailing_list_title_block",
 *   admin_label = @Translation("Mailing List Title block"),
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

  public function editTitleLink() {

    $url = Url::fromRoute('dvm_mailing_list.edit_title', [
      'group' => \Drupal::routeMatch()->getParameter('group')->id()
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

    if($group = \Drupal::routeMatch()->getParameter('group')) {
      /** @var Group $group */
      if ($group->bundle() == 'mailing_list' && $group->access('update')) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}