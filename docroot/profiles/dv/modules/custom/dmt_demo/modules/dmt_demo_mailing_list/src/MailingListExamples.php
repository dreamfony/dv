<?php
namespace Drupal\dmt_demo_mailing_list;

use Drupal\dmt_demo_mailing_list\Queue\ProcessQueue;
use Drupal\dmt_demo_mailing_list\Yaml\YmlParser;
use Drupal\dvm_mailing_list\MailingListAnswer;
use Drupal\user\Entity\User;
use Drupal\group\Entity\Group;
use Drupal\dvm_mailing_list\MailingList;
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
   * @var \Drupal\dvm_mailing_list\MailingList
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
   * @var \Drupal\dvm_mailing_list\MailingListAnswer
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
   * @param \Drupal\dvm_mailing_list\MailingList $mailing_list
   * @param \Drupal\dvm_mailing_list\MailingListAnswer $mailing_list_answer
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

    // Loop through the content and try to create new entries.
    foreach ($this->groups as $uuid => $group_data) {

      /// skip if item is not enabled
      if($group_data['status'] === false) {
        continue;
      }

      // Check if the group does not exist yet.
      $existing_groups = $this->groupStorage->loadByProperties(array('uuid' => $uuid));

      // Loop through the groups.
      foreach ($existing_groups as $key => $group) {
        // And delete them.
        $group->delete();
      }

      $user = $this->getUser($group_data['user']);

      $group_object = $this->createGroup($uuid, $group_data, $user);

      if ($group_object instanceof Group) {

        // add recipients
        if( isset($group_data['recipients']) ) {
          $this->addMembers($group_object, $group_data);
        }

        // create questions
        if($group_data['state'] !== 'new'){
          $this->createQuestions($group_object, $user);
        }

        // send for approval
        if($group_data['state'] == 'email'){
          $this->mailingList->sendForApproval($group_object);
        }

        // approve
        if($group_data['state'] == 'approved'){
          $this->mailingList->sendForApproval($group_object);
          $this->mailingList->approve($group_object);
        }

        // activities created
        if($group_data['state'] == 'activities_created'){
          $this->mailingList->sendForApproval($group_object);
          $this->mailingList->approve($group_object);

          $this->processQueue->queueProcess('activity_logger_message');
          $this->processQueue->queueProcess('activity_creator_logger');
          $this->processQueue->queueProcess('activity_creator_activities');
        }

        // sent
        if($group_data['state'] == 'sent'){
          $this->mailingList->sendForApproval($group_object);
          $this->mailingList->approve($group_object);

          $this->processQueue->queueProcess('activity_logger_message');
          $this->processQueue->queueProcess('activity_creator_logger');
          $this->processQueue->queueProcess('activity_creator_activities');
          $this->processQueue->queueProcess('activity_send_email_worker');
        }

        // partially answers
        if($group_data['state'] == 'partially_answered'){
          $this->mailingList->sendForApproval($group_object);
          $this->mailingList->approve($group_object);

          $this->processQueue->queueProcess('activity_logger_message');
          $this->processQueue->queueProcess('activity_creator_logger');
          $this->processQueue->queueProcess('activity_creator_activities');
          $this->processQueue->queueProcess('activity_send_email_worker');

          $activities = $this->getActivitiesByGroup($group_object, 10);

          $values = array_values($this->comments)[0];

          foreach ($activities as $activity) {
            $this->mailingListAnswer->createAnswerFromActivity($activity, $values);
          }
        }

        // fully answers
        if($group_data['state'] == 'fully_answered'){
          $this->mailingList->sendForApproval($group_object);
          $this->mailingList->approve($group_object);

          $this->processQueue->queueProcess('activity_logger_message');
          $this->processQueue->queueProcess('activity_creator_logger');
          $this->processQueue->queueProcess('activity_creator_activities');
          $this->processQueue->queueProcess('activity_send_email_worker');

          $activities = $this->getActivitiesByGroup($group_object);

          $values = array_values($this->comments)[0];

          foreach ($activities as $activity) {
            $this->mailingListAnswer->createAnswerFromActivity($activity, $values);
          }
        }

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
    $group_object = Group::create([
      'uuid' => $uuid,
      'type' => $group_data['group_type'],
      'label' => $group_data['title'],
      'uid' => $user->id(),
      'created' => $grouptime,
      'changed' => $grouptime,
    ]);
    $group_object->save();

    return $group_object;
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
      foreach ($groups as $key => $group) {
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
