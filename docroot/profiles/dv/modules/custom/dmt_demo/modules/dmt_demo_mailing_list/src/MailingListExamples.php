<?php
namespace Drupal\dmt_demo_mailing_list;

use Drupal\dmt_demo_mailing_list\Queue\ProcessQueue;
use Drupal\dmt_demo_mailing_list\Yaml\YmlParser;
use Drupal\dmt_mailing_list\MailingListAnswer;
use Drupal\user\Entity\User;
use Drupal\group\Entity\Group;
use Drupal\dmt_mailing_list\MailingList;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;

/**
 * Implements Demo content for Groups.
 */
class MailingListExamples {

  /** @var mixed */
  protected $groups;
  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;
  /**
   * The entity storage.
   *
   * @var \Drupal\Core\entity\EntityStorageInterface
   */
  protected $groupStorage;

  /**
   * @var \Drupal\dmt_mailing_list\MailingList
   */
  protected $mailingList;

  /**
   * @var
   */
  protected $nodes;

  /**
   * @var User
   */
  protected $user;

  /**
   * @var \Drupal\dmt_mailing_list\MailingListAnswer
   */
  protected $mailingListAnswer;

  /**
   * @var \Drupal\dmt_demo_mailing_list\Queue\ProcessQueue
   */
  protected $processQueue;

  /**
   * @var \Drupal\Core\entity\EntityStorageInterface
   */
  protected $activityStorage;

  /**
   * MailingListExamples constructor.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_manager
   * @param \Drupal\dmt_mailing_list\MailingList $mailing_list
   * @param \Drupal\dmt_mailing_list\MailingListAnswer $mailing_list_answer
   * @param \Drupal\dmt_demo_mailing_list\Yaml\YmlParser $yml_parser
   * @param \Drupal\dmt_demo_mailing_list\Queue\ProcessQueue $process_queue
   */
  public function __construct(EntityTypeManager $entity_manager, MailingList $mailing_list, MailingListAnswer $mailing_list_answer, YmlParser $yml_parser, ProcessQueue $process_queue) {
    $this->userStorage = $entity_manager->getStorage('user');
    $this->groupStorage = $entity_manager->getStorage('group');
    $this->activityStorage = $entity_manager->getStorage('activity');
    $this->mailingList = $mailing_list;
    $yml_data = $yml_parser;
    $this->groups = $yml_data->parseFile('MailingLists.yml');
    $this->nodes = $yml_data->parseFile('Questions.yml');
    $this->comments = $yml_data->parseFile('Answers.yml');
    $this->processQueue = $process_queue;
    $this->mailingListAnswer = $mailing_list_answer;
  }

  /**
   * Function to create content.
   */
  public function createContent() {

    // Remove all existing groups before creating new ones
    $this->removeContent();

    // Loop through the content and try to create new entries.
    foreach ($this->groups as $uuid => $group_data) {

      /// skip if item is not enabled
      if($group_data['status'] === false) {
        continue;
      }

      $user = $this->getUser($group_data['user']);

      $group_object = $this->createGroup($uuid, $group_data, $user);

      if ($group_object instanceof Group) {

        // add recipients
        if( isset($group_data['recipients']) ) {
          $this->addMembers($group_object, $group_data);
        }

        // create questions
        if($group_data['add_questions']){
          $this->createQuestions($group_object, $user);
        }

        // Loop trough states save each and process queues
        foreach ($group_data['states'] as $state) {
          // skip draft state
          if($state == 'draft') {
            continue;
          }

          $group_object->set('moderation_state', $state);
          $group_object->save();

          // process all activity queues before switching to next state
          $this->processQueue->queueProcess('activity_logger_message');
          $this->processQueue->queueProcess('activity_creator_logger');
          $this->processQueue->queueProcess('activity_creator_activities');
          $this->processQueue->queueProcess('activity_send_email_worker');
        }

        // process answers if present
        if(isset($group_data['answers'])) {
          $answer_count = $group_data['answers'];

          if($answer_count > 0) {
            $activities = $this->getActivitiesByGroup($group_object, $answer_count);
          } else {
            $activities = $this->getActivitiesByGroup($group_object);
          }

          $values = array_values($this->comments)[0];

          foreach ($activities as $activity) {
            $this->mailingListAnswer->createAnswerFromActivity($activity, $values);
          }
        }

        drush_log(dt('Create: @title', ['@title' => $group_data['title']]), 'success');
      }
    }
    return TRUE;
  }

  public function getActivitiesByGroup(Group $group, $limit = FALSE) {

    $activities = [];

    $query = \Drupal::entityQuery('activity')
      ->condition('field_activity_mailing_list.target_id', $group->id());

    if($limit) {
      $query->range(0, $limit);
    }

    $results = $query->execute();

    foreach ($results as $id) {
      $activities[] = $this->activityStorage->load($id);
    }

    return $activities;

  }

  public function getUser($user_name) {

    if ($this->user instanceof User) {
      return $this->user;
    }

    $this->createUser($user_name);
    return $this->user;
  }

  public function createUser($user_name) {

    $user = user_load_by_name($user_name);

    if(!$user) {
        $user = User::create(array(
          'name' => $user_name,
          'mail' => $user_name . '@test.com',
          'status' => 1,
          'pass' => $user_name,
          'personas' => array('journalist')
        ));
        $user->save();
    }

    $this->user = $user;
  }

  public function createGroup($uuid, $group_data, User $user) {
    // Calculate data.
    $grouptime = $this->createDate($group_data['created']);
    // Let's create some groups.
    $group = Group::create([
      'uuid' => $uuid,
      'type' => $group_data['group_type'],
      'label' => $group_data['title'],
      'uid' => $user->id(),
      'created' => $grouptime,
      'changed' => $grouptime,
    ]);

    $group->set('moderation_state', 'draft');

    $group->save();

    return $group;
  }

  /**
   * @param $group_object
   * @param $group_data
   */
  public function addMembers(Group $group_object, array $group_data) {
    // add recipients
    foreach ($group_data['recipients'] as $area_of_activity_id) {
      $query = \Drupal::entityQuery('group');
      $query->condition('type', 'area_of_activity');
      $query->condition('field_area_of_activity_id', $area_of_activity_id);
      $result = $query->execute();

      if($result) {

        // add target_id
        foreach ($result as $r) {
          $result_target_id[$r] = ['target_id' => $r];
        }

        $this->mailingList->addRecipients($result_target_id, $group_object->id());
      }
    }
  }

  public function createQuestions(Group $group_object, User $user) {
    // add questions
    foreach ($this->nodes as $question_uuid => $question) {
      $node = Node::create([
        'type' => 'question',
        'title' => '',
        'body' => [
          'summary' => '',
          'value' => $question['body'],
          'format' => 'basic_html',
        ],
        'uid' => $user->id(),
        'created' => REQUEST_TIME,
        'changed' => REQUEST_TIME,
        'field_question_comment_type' => $question['field_question_comment_type']
      ]);
      $node->save();
      // add node to created group
      $group_object->addContent($node, 'group_node:' . $node->bundle());
    }
  }

  /**
   * Function to remove content.
   */
  public function removeContent() {
    // Loop through the content and try to create new entries.
    foreach ($this->groups as $uuid => $group) {
      // Load the groups from the uuid.
      $groups = $this->groupStorage->loadByProperties(array('uuid' => $uuid));
      // Loop through the groups.
      foreach ($groups as $group) {
        $activities = $this->getActivitiesByGroup($group);
        foreach ($activities as $activity) {
          $activity->delete();
        }
        // And delete them.
        $group->delete();
      }
    }
  }

  /**
   * Function to calculate the date.
   */
  public function createDate($date_string) {
    // Split from delimiter.
    $timestamp = explode('|', $date_string);
    $date = strtotime($timestamp[0]);
    $date = date("Y-m-d", $date) . "T" . $timestamp[1] . ":00";
    return strtotime($date);
  }

}
