<?php

namespace Drupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Environment\Environment;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Drupal\profile\Entity\Profile;
use Drupal\group\Entity\Group;
use Drupal\DrupalExtension\Hook\Scope\EntityScope;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class GroupContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * @var \Drupal\DrupalExtension\Context\DrupalContext
   */
  protected $drupalContext;

  /**
   * Every scenario gets its own context instance.
   *
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {

  }

  /**
   * Gives us access to the other contexts so we can access their properties.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    /** @var Environment $environment */
    $environment = $scope->getEnvironment();

    $this->drupalContext = $environment->getContext('Drupal\DrupalExtension\Context\DrupalContext');
    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
  }

  /**
   * Creates group of a given type provided in the form:
   * | title    | description     | author   | type        | language
   * | My title | My description  | username | open_group  | en
   * | ...      | ...             | ...      | ...         | ...
   *
   * @Given groups:
   */
  public function createGroups(TableNode $groupsTable) {
    foreach ($groupsTable->getHash() as $groupHash) {
      $group = (object) $groupHash;
      $this->groupCreate($group);
    }
  }

  /**
   * Create a group.
   *
   * @param $group
   * @return mixed
   * @throws \Exception
   */
  public function groupCreate($group) {
    $account = user_load(1);

    // Let's create some groups.
    $group_object = Group::create([
      'langcode' => $group->language,
      'uid' => $account->id(),
      'type' => $group->type,
      'label' => $group->title
    ]);

    $group_object->save();

    if(!$group_object->id()) {
      throw new \Exception("Survey with the title ". $group->label . " was not created.");
    }

    return $group_object;
  }

  /**
   * @AfterScenario @groups
   */
  public function cleanupGroups(AfterScenarioScope $scope) {
    $query = \Drupal::entityQuery('group')
      ->condition('label', array(
        'Test %'
      ), 'LIKE');
    $group_ids = $query->execute();
    $groups = entity_load_multiple('group', $group_ids);
    foreach ($groups as $group) {
      $group->delete();
    }
  }

  /**
   * Load Group by Label and Title
   *
   * @param $label
   * @param $type
   * @return \Drupal\group\Entity\GroupInterface|object
   * @throws \Exception
   */
  public function loadGroupByLabelAndType($label, $type) {
    $group = (object) [];

    $query = \Drupal::entityQuery('group');
    $query->condition('type', $type);
    $query->condition('label', $label);
    $results = $query->execute();

    foreach ($results as $id) {
      /** @var \Drupal\group\Entity\GroupInterface $group */
      $group = Group::load($id);
    }

    if(!$group->id()) {
      throw new \Exception("Group with the title ". $label. " not found.");
    }

    return $group;
  }

}

