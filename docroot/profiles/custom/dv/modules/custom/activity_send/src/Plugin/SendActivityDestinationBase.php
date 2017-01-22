<?php

/**
 * @file
 * Contains \Drupal\activity_send\Plugin\ActivityDestination\SendActivityDestinationBase.
 */

namespace Drupal\activity_send\Plugin;

use Drupal\activity_creator\Plugin\ActivityDestinationBase;
use Drupal\user\UserInterface;

/**
 * Base class for Activity send destination plugins.
 */
class SendActivityDestinationBase extends ActivityDestinationBase {

  /**
   * Returns message templates for which given destination is enabled.
   *
   * @param $destination
   * @return array
   */
  public static function getSendMessageTemplates($destination) {
    $email_message_templates = [];
    /** @var \Drupal\message\MessageTemplateInterface[] $message_templates */
    $message_templates = \Drupal::entityTypeManager()
      ->getStorage('message_template')
      ->loadMultiple();
    foreach ($message_templates as $message_template) {
      $destinations = $message_template->getThirdPartySetting('activity_logger', 'activity_destinations', NULL);
      if (is_array($destinations) && in_array($destination, $destinations)) {
        $email_message_templates[$message_template->id()] = $message_template->getDescription();
      }
    }
    return $email_message_templates;
  }

  /**
   * Returns notification settings of given user.
   *
   * @param $destination
   * @param \Drupal\user\UserInterface $account
   * @return mixed
   */
  public static function getSendUserSettings($destination, UserInterface $account) {
    $query = \Drupal::database()->select('user_activity_send', 'uas');
    $query->fields('uas', ['message_template', 'status']);
    $query->condition('uas.uid', $account->id());
    $query->condition('uas.destination', $destination);
    return $query->execute()->fetchAllKeyed();
  }

  /**
   * Set notification settings for given user.
   *
   * @param $destination
   * @param \Drupal\user\UserInterface $account
   * @param $values
   */
  public static function setSendUserSettings($destination, UserInterface $account, $values) {
    if (is_object($account) && !empty($values)) {
      foreach ($values as $message_template => $status) {
        $query = \Drupal::database()->merge('user_activity_send');
        $query->fields([
          'uid' => $account->id(),
          'destination' => $destination,
          'message_template' => $message_template,
          'status' => $status
        ]);
        $query->keys([
          'uid' => $account->id(),
          'destination' => $destination,
          'message_template' => $message_template,
        ]);
        $query->execute();
      }
    }
  }

  /**
   * Returns target account.
   *
   * @param $activity
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public static function getSendTargetUser($activity) {
    // Get target account.
    if (isset($activity->field_activity_recipient_user) && !empty($activity->field_activity_recipient_user->target_id)) {
      $target_id = $activity->field_activity_recipient_user->target_id;
      $target_account = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->load($target_id);
      return $target_account;
    }
  }

  /**
   * Check if user last activity was more than few minutes ago.
   *
   * @param \Drupal\user\UserInterface $account
   * @return bool
   */
  public static function isUserOffline(UserInterface $account) {
    $query = \Drupal::database()->select('sessions', 's');
    $query->addField('s', 'timestamp');
    $query->condition('s.uid', $account->id());
    $last_activity_time = $query->execute()->fetchField();

    $offline_window = \Drupal::config('activity_send.settings')->get('activity_send_offline_window');
    $request_time = \Drupal::time()->getRequestTime();
    $current_time = $request_time - $offline_window;

    return (empty($last_activity_time) || $last_activity_time < $current_time);
  }

}
