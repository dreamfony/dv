<?php

/**
 * @file
 * Contains \Drupal\activity_send_email\Plugin\ActivityDestination\EmailActivityDestination.
 */

namespace Drupal\activity_send_email\Plugin\ActivityDestination;

use Drupal\activity_send\Plugin\SendActivityDestinationBase;
use Drupal\message\MessageInterface;

/**
 * Provides a 'EmailActivityDestination' activity destination.
 *
 * @ActivityDestination(
 *  id = "email",
 *  label = @Translation("Email"),
 * )
 */
class EmailActivityDestination extends SendActivityDestinationBase {

  /**
   * {@inheritdoc}
   */
  public static function getSendEmailMessageTemplates() {
    return parent::getSendMessageTemplates('email');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSendEmailUserSettings($account) {
    return parent::getSendUserSettings('email', $account);
  }

  /**
   * {@inheritdoc}
   */
  public static function setSendEmailUserSettings($account, $values) {
    parent::setSendUserSettings('email', $account, $values);
  }

  /**
   * Get field value for 'output_text' field from data array.
   *
   * @param \Drupal\contact\MessageInterface $message
   * @return mixed
   */
  public static function getSendEmailOutputText(MessageInterface $message) {
    $value = NULL;
    if (isset($message)) {
      $value = $message->getText();
      // Text for email.
      if (!empty($value[2])) {
        $text = $value[2];
      }
      // Default text.
      else {
        $text = $value[0];
      }
    }

    return $text;
  }

}
