<?php

/**
 * @file
 * Builds placeholder replacement tokens for message-related data.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;

/**
 * Implements hook_token_info().
 */
function activity_logger_token_info() {
  $type = [
    'name' => t('Message tokens from activity'),
    'description' => t('Tokens from the activity logger module.'),
    'needs-data' => 'message',
  ];

  $message['node-title'] = [
    'name' => t("Node title"),
    'description' => t("The related node title."),
  ];

  $message['gtitle'] = [
    'name' => t("Groups title"),
    'description' => t("The related group title."),
  ];
  $message['gurl'] = [
    'name' => t("Groups url"),
    'description' => t("The related group url."),
  ];

  $message['recipient-user-url'] = [
    'name' => t("Recipient user url"),
    'description' => t("The recipient user url."),
  ];

  $message['node-body'] = [
    'name' => t("Node body"),
    'description' => t("The related node body."),
  ];

  $activity_type = [
    'name' => t('Activity tokens from Activity'),
    'description' => t('Tokens from the activity logger module.'),
    'needs-data' => 'activity',
  ];

  $activity['replyto-email'] = [
    'name' => t("ReplyTo Email"),
    'description' => t("ReplyTo Email"),
  ];

  $activity['recipient-data'] = [
    'name' => t("Recipient data"),
    'description' => t("Information about recipient"),
  ];

  $activity['sender-data'] = [
    'name' => t("Sender data"),
    'description' => t("Information about sender"),
  ];

  return [
    'types' => [
      'message' => $type,
      'activity' => $activity_type
    ],
    'tokens' => [
      'message' => $message,
      'activity' => $activity
    ],
  ];
}

/**
 * Implements hook_tokens().
 */
function activity_logger_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'message' && !empty($data['message'])) {
    /** @var \Drupal\message\Entity\Message $message */
    $message = $data['message'];

    foreach ($tokens as $name => $original) {
      switch ($name) {

        case 'gtitle':
        case 'gurl':
        case 'recipient-user-url':
        case 'node-body':

          if (isset($message->field_message_related_object)) {
            $target_type = $message->field_message_related_object->target_type;
            $target_id = $message->field_message_related_object->target_id;
            $entity = \Drupal::entityTypeManager()
              ->getStorage($target_type)
              ->load($target_id);

            if (is_object($entity)) {
              // If comment get the entity to which the comment is attached.
              if ($entity->getEntityTypeId() === 'comment') {
                $entity = $entity->getCommentedEntity();
              }

              // Try to get the group.
              $group_content = GroupContent::loadByEntity($entity);
              if (!empty($group_content)) {
                $group_content = reset($group_content);
                $group = $group_content->getGroup();
              }

              // If it's a group.. add it in the arguments.
              if (isset($group) && $group instanceof Group) {
                if ($name === 'gtitle') {
                  $replacements[$original] = $group->label();
                }
                if ($name === 'gurl') {
                  $gurl = Url::fromRoute('entity.group.canonical', array(
                    'group' => $group->id(),
                    array()
                  ));
                  $replacements[$original] = $gurl->toString();
                }
              }

              if ($name === 'recipient-user-url') {
                if (!empty($recipient_user)) {
                  $target_stream_url = Url::fromRoute('entity.user.canonical',
                    array('user' => $recipient_user->id()),
                    array('absolute' => TRUE)
                  );
                  $replacements[$original] = $target_stream_url->toString();
                }
              }

              if ($name === 'node-body') {
                $replacements[$original] = $entity->body->value;
              }
            }
          }
          break;
      }
    }
  }

  if($type == 'activity' && !empty($data['activity'])) {
    /** @var \Drupal\activity_creator\Entity\Activity $activity */
    $activity = $data['activity'];

    foreach ($tokens as $name => $original) {
      switch ($name) {

        case 'replyto-email':
          $hash = $activity->get('field_activity_hash')->getString();
          $replacements[$original] = \Drupal::service('activity_send_email.replyto')
            ->getAddress(strlen($hash) > 1 ? $hash : NULL);
          break;

        case 'recipient-data':
          $user_id = $activity->get('field_activity_recipient_user')->target_id;
          $user = \Drupal::entityTypeManager()->getStorage("user")->load($user_id);
          $active_profile = \Drupal::entityTypeManager()->getStorage('profile')->loadByUser($user, "organisation_profile");

          // Designate the field we want to render.
          $field_name = 'field_org_address';
          $display = [
            'label' => 'hidden',
            'type' => 'field_address',
          ];
          // Retrieve a render array for that field with the given view mode.
          $build = $active_profile->$field_name->view($display);
          // Render the result.
          $address = \Drupal::service('renderer')->renderRoot($build);
          $replacements[$original] = \Drupal\Core\Render\Markup::create($active_profile->get('field_org_title')->value . $address);
          break;


        case 'sender-data':
          $user_id = $activity->get('user_id')->target_id;
          $user = \Drupal::entityTypeManager()->getStorage("user")->load($user_id);

          $replacements[$original] = $user->get('name')->value;
          break;
      }
    }
  }



  return $replacements;
}
