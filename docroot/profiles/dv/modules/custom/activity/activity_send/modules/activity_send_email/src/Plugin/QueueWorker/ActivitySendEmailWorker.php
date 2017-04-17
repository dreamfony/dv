<?php

/**
 * @file
 * Contains \Drupal\activity_send_email\Plugin\QueueWorker\ActivitySendEmailWorker.
 */

namespace Drupal\activity_send_email\Plugin\QueueWorker;

use Drupal\activity_send_email\Plugin\ActivityDestination\EmailActivityDestination;
use Drupal\activity_send\Plugin\QueueWorker\ActivitySendWorkerBase;
use Drupal\activity_creator\Entity\Activity;
use Drupal\message\MessageInterface;
use Drupal\message\Entity\Message;
use Drupal\dvm_user\Helper\UserSettings;
use Drupal\user\Entity\User;


/**
 * An activity send email worker.
 *
 * @QueueWorker(
 *   id = "activity_send_email_worker",
 *   title = @Translation("Process 4 activity_send_email_worker."),
 *   cron = {"time" = 60}
 * )
 *
 * This QueueWorker is responsible for sending emails from the queue
 */
class ActivitySendEmailWorker extends ActivitySendWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {

    // First make sure it's an actual Activity entity.
    if (!empty($data['entity_id']) && $activity = Activity::load($data['entity_id'])) {
      // Get target account.
      /** @var User $target_account */
      $target_account = EmailActivityDestination::getSendTargetUser($activity);
      // Check if user last activity was more than few minutes ago.
      if (is_object($target_account) && EmailActivityDestination::isUserOffline($target_account)) {
        // Get Message Template id.
        $message = Message::load($activity->field_activity_message->target_id);
        $message_template_id = $message->getTemplate()->id();

        // Get email notification settings of active user.
        $user_email_settings = EmailActivityDestination::getSendEmailUserSettings($target_account);

        // Check if email notifications is enabled for this kind of activity.
        // If user don't change it's enabled by default.
        if ((!isset($user_email_settings[$message_template_id])
            || (isset($user_email_settings[$message_template_id]) && $user_email_settings[$message_template_id] == 1))
          && isset($activity->field_activity_output_text)
        ) {
          // Send Email
          $langcode = \Drupal::currentUser()->getPreferredLangcode();

          $mail_manager = \Drupal::service('plugin.manager.mail');
          $mail = $mail_manager->mail(
            'activity_send_email',
            'activity_send_email',
            $this->getEmailRecipient($target_account),
            $langcode,
            $this->getEmailParams($message, $activity, $data),
            $send = TRUE
          );
        }
      }
    }
  }

  /**
   * Get Email Params.
   *
   * @param \Drupal\message\MessageInterface $message
   * @param \Drupal\activity_creator\Entity\Activity $activity
   * @param $data
   * @return mixed
   */
  protected function getEmailParams(MessageInterface $message, Activity $activity, $data) {
    $params['body'] = $this->getEmailBody($message, $activity);

    $hash = $activity->get('field_activity_hash')->getString();
    $reply_to = \Drupal::service('activity_send_email.replyto')->getAddress( strlen($hash) > 1 ? $hash : NULL );

    $params['h:Reply-To'] = $reply_to;
    $params['h:Message-Id'] = $reply_to;
    $params['v:entity_id'] = $data['entity_id'];
    $params['v:hash'] = $hash;
//          $params['v:short_code'] = $data['entity_id'];
    $params['o:tag'] = ['survey', 'shortcode'];
    $params['o:tracking-opens'] = 'yes';
    $params['o:tracking-clicks'] = 'yes';
    $params['o:tracking'] = 'yes';

    return $params;
  }

  /**
   * Get Email Body.
   *
   * @param \Drupal\message\MessageInterface $message
   * @param \Drupal\activity_creator\Entity\Activity $activity
   * @return mixed|string
   */
  protected function getEmailBody(MessageInterface $message, Activity $activity) {
    $body = EmailActivityDestination::getSendEmailOutputText($message);

    // replace tokens from activity before sending
    $activity_token_options = [
      'langcode' => $message->language()->getId(),
      'clear' => false,
    ];

    $body = \Drupal::token()
      ->replace($body, ['activity' => $activity], $activity_token_options);

    return $body;
  }

  /**
   * Get target account.
   *
   * @param \Drupal\user\Entity\User $target_account
   * @return string
   */
  protected function getEmailRecipient(User $target_account) {
    // get organisation email in case user role is organisation
    if ($target_account->hasRole(UserSettings::ROLE_ORGANISATION)){
      $profile = \Drupal::entityManager()->getStorage('profile')
        ->loadByUser($target_account, UserSettings::PROFILE_ORGANISATION);
      if($profile) {
        return $profile->get('field_org_email')->getValue()[0]['value'];
      }
    }
    else {
      return $target_account->getEmail();
    }
  }

}
