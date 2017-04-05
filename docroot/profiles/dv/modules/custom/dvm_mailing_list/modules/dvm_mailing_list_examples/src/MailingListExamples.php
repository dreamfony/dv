<?php
namespace Drupal\dvm_mailing_list_examples;

use Drupal\dvm_mailing_list_examples\Yaml\YmlParser;
use Drupal\user\Entity\User;
use Drupal\group\Entity\Group;
use Drupal\dvm_mailing_list\MailingList;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
   * MailingListExamples constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_manager
   * @param \Drupal\dvm_mailing_list\MailingList $mailing_list
   * @param \Drupal\dvm_mailing_list_examples\Yaml\YmlParser $yml_parser
   */
  public function __construct(EntityTypeManager $entity_manager, MailingList $mailing_list, YmlParser $yml_parser) {
    $this->userStorage = $entity_manager->getStorage('user');
    $this->groupStorage = $entity_manager->getStorage('group');
    $this->mailingList = $mailing_list;
    $yml_data = $yml_parser;
    $this->groups = $yml_data->parseFile('MailingLists.yml');
    $this->nodes = $yml_data->parseFile('Questions.yml');
  }

  /**
   * Function to create content.
   */
  public function createContent() {

    // Loop through the content and try to create new entries.
    foreach ($this->groups as $uuid => $group) {
      // Must have uuid and same key value.
      if ($uuid !== $group['uuid']) {
        continue;
      }
      // Check if the group does not exist yet.
      $existing_groups = $this->groupStorage->loadByProperties(array('uuid' => $uuid));
      $existing_group = reset($existing_groups);
      // If it already exists, leave it.
      if ($existing_group) {
        continue;
      }

      $user_id = user_load_by_name($group['user']);

      // Calculate data.
      $grouptime = $this->createDate($group['created']);
      // Let's create some groups.
      $group_object = Group::create([
        'uuid' => $group['uuid'],
        'langcode' => $group['language'],
        'type' => $group['group_type'],
        'label' => $group['title'],
        'uid' => $user_id,
        'created' => $grouptime,
        'changed' => $grouptime,
      ]);
      $group_object->save();

      if ($group_object instanceof Group) {

        // add recipients
        foreach ($group['members'] as $area_of_activity_id) {
          $query = \Drupal::entityQuery('group');
          $query->condition('type', 'area_of_activity');
          $query->condition('field_area_of_activity_id', $area_of_activity_id);
          $result = $query->execute();

          $this->mailingList->addRecipients($result, $group_object->id());
        }

        // add questions
        foreach ($this->nodes as $node_uuid => $question) {
          $node = Node::create([
            'uuid' => $question['uuid'],
            'type' => 'question',
            'langcode' => $question['language'],
            'title' => '',
            'body' => [
              'summary' => '',
              'value' => $question['body'],
              'format' => 'basic_html',
            ],
            'uid' => $user_id,
            'created' => REQUEST_TIME,
            'changed' => REQUEST_TIME,
            'field_question_comment_type' => $question['field_question_comment_type']
          ]);
          $node->save();
          // add node to created group
          $group_object->addContent($node, 'group_node:' . $node->bundle());
        }

      }

      /// @todo Approve!
      /// @todo Run Queues!

      return $group_object->id();
    }

  }
  /**
   * Function to remove content.
   */
  public function removeContent() {
    // Loop through the content and try to create new entries.
    foreach ($this->groups as $uuid => $group) {
      // Must have uuid and same key value.
      if ($uuid !== $group['uuid']) {
        continue;
      }
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
