<?php

namespace Drupal\dmt_mailing_list;

use Drupal\activity_creator\Entity\Activity;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;
use Drupal\comment\Entity\Comment;
use Drupal\comment\CommentInterface;

/**
 * Class Mailing List Answers
 * @package Drupal\dmt_mailing_list
 */
class MailingListAnswer {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * MailingListAnswers constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   */
  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Create Answer From Activity
   *
   * @param \Drupal\activity_creator\Entity\Activity $activity
   * @param array $values
   */
  public function createAnswerFromActivity(Activity $activity, array $values) {
    /** @var Node $content */
    $content = $activity->get('field_activity_entity')->entity;

    $comment_type = $content->get('field_content_comment_type')
      ->getString();

    $values = $values +
      [
        'entity_id' => $content->id(),
        'comment_type' => $comment_type,
        'pid' => $this->getAnswerPid($activity)
      ];

    // if user has not being set get user from activity
    if(empty($values['user'])) {
      $values['uid'] = $activity->get('field_activity_recipient_user')->entity;
    }

    // Create a comment.
    $answer = $this->createAnswer($values);

    // set activity status to answered
    $activity->setModerationState('answered');

    // set comment reply
    $activity->field_activity_reply[] = $answer->id();

    // save activity
    $activity->save();
  }

  /**
   * Get answer parent activity comment id
   *
   * @param \Drupal\activity_creator\Entity\Activity $activity
   * @return mixed
   */
  private function getAnswerPid(Activity $activity) {
    $query = \Drupal::entityQuery('comment')
      ->condition('field_comment_activity.target_id', $activity->id());

    $result = $query->execute();

    return key($result);
  }


  /**
   * Create new Mailing List Answer.
   *
   * @return int|mixed|null|string
   */
  private function createAnswer(array $values) {
    // Create a comment entity.
    $comment = Comment::create([
      'entity_type' => 'node',
      'entity_id' => $values['entity_id'],
      'uid' => $values['uid'],
      'subject' => $values['subject'],
      'pid' => $values['pid'],
      'comment_body' => [
        'value' => $values['body'],
        'format' => 'plain_text',
      ],
      'field_name' => 'field_content_answers', // field comment is attached to
      'comment_type' => $values['comment_type'],
      'status' => CommentInterface::PUBLISHED,
    ]);

    if (!empty($values['files'])) {
      $comment->set('field_c_attachments', $values['files']);
    }

    $comment->save();

    return $comment;
  }

}
