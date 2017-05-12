<?php

namespace Drupal\activity_viewer\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a 'Notifications' block.
 *
 * @Block(
 *  id = "notifications_block",
 *  admin_label = @Translation("Notifications block"),
 * )
 */
class NotificationsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $links = [];

    $account = \Drupal::currentUser();
    if ($account->id() !== 0) {

      $notifications_view = views_embed_view('activity_stream_notifications', 'block_1');
      $notifications = \Drupal::service('renderer')
        ->render($notifications_view);

      $account_notifications = \Drupal::service('activity_creator.activity_notifications');
      $num_notifications = count($account_notifications->getNotifications($account, array(ACTIVITY_STATUS_SENT, ACTIVITY_STATUS_PENDING)));


      if ($num_notifications === 0) {
        $notifications_icon = 'icon-notifications_none';
        $label_classes = 'hidden';
      }
      else {
        $notifications_icon = 'icon-notifications';
        $label_classes = 'badge badge-accent badge--pill';

        if ($num_notifications > 99) {
          $num_notifications = '99+';
        }
      }

      $links['notifications'] = array(
        'classes' => 'dropdown notification-bell',
        'link_attributes' => 'data-toggle=dropdown aria-expanded=true aria-haspopup=true role=button',
        'link_classes' => 'dropdown-toggle clearfix',
        'icon_classes' => $notifications_icon,
        'title' => $this->t('Notification Centre'),
        'label' => (string) $num_notifications,
        'title_classes' => $label_classes,
        'url' => '#',
        'below' => $notifications,
      );
    }

    return [
      '#theme' => 'notifications_block',
      '#links' => $links,
      '#cache' => array(
        'contexts' => array('user'),
      ),
      '#attached' => array(
        'library' => array(
          'activity_creator/activity_creator.notifications',
        ),
      ),
    ];
  }

}
