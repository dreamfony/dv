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
    $referenced_object = $activity->get('field_activity_entity')->getValue();

    $ref_entity_type = $referenced_object['0']['target_type'];
    $ref_entity_id = $referenced_object['0']['target_id'];

    /** @var Node $question */
    $question = $this->entityTypeManager
      ->getStorage($ref_entity_type)
      ->load($ref_entity_id);

    $comment_type = $question->get('field_question_comment_type')
      ->getString();


    $values = $values +
      [
        'entity_id' => $ref_entity_id,
        'comment_type' => $comment_type
      ];

    // if user has not being set get user from activity
    if(empty($values['user'])) {
      $values['uid'] = $activity->get('field_activity_recipient_user')->getValue()[0]['target_id'];
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
      'comment_body' => [
        'value' => $values['body'],
        'format' => 'basic_html',
      ],
      'field_name' => 'field_question_answers', // wtf is this?
      'comment_type' => $values['comment_type'],
      'status' => CommentInterface::PUBLISHED,
    ]);

    if (!empty($values['files'])) {
      $comment->set('field_c_attachments', $values['files']);
    }

    $comment->save();

    return $comment;
  }

  /**
   * Get answer count.
   *
   * @param $group_id
   * @param $user_id
   * @param bool $status
   * @return array|int
   */
  public function getAnswerCount($group_id, $user_id = FALSE, $status = FALSE) {
    $query = \Drupal::entityQuery('activity')
      ->condition('field_activity_mailing_list.target_id', $group_id);

    if($user_id) {
      $query->condition('field_activity_recipient_user.target_id', $user_id);
    }

    if ($status) {
      /** @see activity_creator_query_cm_states_alter */
      $query->addTag('cm_states');
      $query->addMetaData('states', [$status]);
    }

    $count = $query->count()->execute();

    return $count;
  }

}
