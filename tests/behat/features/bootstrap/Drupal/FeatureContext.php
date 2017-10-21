<?php

namespace Drupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Environment\Environment;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Drupal\profile\Entity\Profile;
use Drupal\group\Entity\Group;
use Drupal\DrupalExtension\Hook\Scope\EntityScope;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

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
   * @BeforeScenario
   */
  public function beforeScenario(BeforeScenarioScope $scope)
  {
    $this->getSession()->getDriver()->resizeWindow(1200, 2000);
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
   * Creates and authenticates a user with the given persona.
   *
   * @Given I am logged in as a user with the :persona persona
   * @Given I am logged in as a/an :persona
   */
  public function assertAuthenticatedByPersona($persona) {
    // Check if a user with this role is already logged in.
    if (!$this->loggedInWithPersona($persona)) {
      // Create user (and project)
      $user = (object) array(
        'name' => $this->getRandom()->name(8),
        'pass' => $this->getRandom()->name(16),
        'personas' => array('journalist'),
      );
      $user->mail = "{$user->name}@example.com";

      $this->userCreate($user);

      // Login.
      $this->login();
    }
  }

  /**
   * User with a given persona is already logged in.
   *
   * @param string $role
   *   A single role, or multiple comma-separated roles in a single string.
   *
   * @return boolean
   *   Returns TRUE if the current logged in user has this role (or roles).
   */
  public function loggedInWithPersona($persona) {
    return $this->loggedIn() && $this->user && isset($this->user->personas) && in_array($persona, $this->user->personas);
  }


  /**
   * @Given I wait for :seconds second/seconds
   */
  public function iWaitForOneSecond($seconds) {
    sleep($seconds);
  }

  /**
   * @When I select the first autocomplete option for :prefix on the :field field
   */
  public function iSelectFirstAutocomplete($prefix, $field) {
    $session = $this->getSession();
    $element = $session->getPage()->findField($field);
    if (empty($element)) {
      throw new ElementNotFoundException($session, NULL, 'named', $field);
    }
    $element->setValue($prefix);
    $element->focus();
    $xpath = $element->getXpath();
    $driver = $session->getDriver();
    // autocomplete.js uses key down/up events directly.
    // Press the down arrow to open the autocomplete options.
    $driver->keyDown($xpath, 40);
    $driver->keyUp($xpath, 40);
    $this->minkContext->iWaitForAjaxToFinish();
    // Select the first option.
    $driver->keyDown($xpath, 40);
    $driver->keyUp($xpath, 40);
    // Press the Enter key to confirm selection, copying the value into the field.
    $driver->keyDown($xpath, 13);
    $driver->keyUp($xpath, 13);
    $this->minkContext->iWaitForAjaxToFinish();
  }

  /**
   * Returns fixed step argument (with \\" replaced back to ").
   *
   * @param string $argument
   *
   * @return string
   */
  protected function fixStepArgument($argument)
  {
    return str_replace('\\"', '"', $argument);
  }

  /**
   * @Then I should see the text :text exactly :times times
   */
  public function iShouldSeeTextSoManyTimes($text, $times)
  {
    $content = $this->getSession()->getPage()->getText();
    $found = substr_count($content, $text);
    if ($times != $found) {
      throw new \Exception('Found '.$found.' occurences of "'.$text.'" when expecting '.$times);
    }
  }

  /**
   * Checks, that form field with specified id|name|label|value has specified value in the region
   *
   * @Then the :field field should contain :value in the :region region
   */
  public function assertFieldValueRegion($field, $value, $region) {
    $field = $this->fixStepArgument($field);
    $value = $this->fixStepArgument($value);
    $region = $this->fixStepArgument($region);

    $region = $this->minkContext->getRegion($region);

    $this->assertSession()->fieldValueEquals($field, $value, $region);
  }

  /**
   * Hook into user creation to add profile fields `@afterUserCreate`
   *
   * @afterUserCreate
   */
  public function alterUserParameters(EntityScope $event) {
    $account = $event->getEntity();
    // Get profile of current user.
    if (!empty($account->uid)) {
      $user_account = \Drupal::entityTypeManager()->getStorage('user')->load($account->uid);
      $storage = \Drupal::entityTypeManager()->getStorage('profile');
      if (!empty($storage)) {
        $user_profile = $storage->loadByUser($user_account, 'profile', TRUE);
        if ($user_profile) {
          // Set given profile field values.
          foreach ($user_profile->toArray() as $field_name => $value) {
            if (isset($account->{$field_name})) {
              $user_profile->set($field_name, $account->{$field_name});
            }
          }
          $user_profile->save();
        }
      }
    }
  }

  /**
   * Creates group of a given type provided in the form:
   * | name     | mail            | address                       |
   * | My title | test@test.com   | 10000 Zagreb, Trg sv. Marka 6 |
   * | ...      | ...             | ...                           |
   *
   * @Given organisations:
   */
  public function createOrganisations(TableNode $orgTable) {


    $group =  (object) [
      'language' => 'en',
      'title' => 'Test Activity Group',
      'type' => 'area_of_activity'
    ];

    $activity_group = $this->groupCreate($group);

    foreach ($orgTable->getHash() as $orgHash) {
      $org = (object) $orgHash;
      $org_user = $this->orgCreate($org);

      $activity_group->addMember($org_user, ['group_roles' => [$activity_group->bundle() . '-organisation']]);
    }
  }

  /**
   * Create a organisation.
   *
   * @param $org
   * @return mixed
   * @throws \Exception
   */
  public function orgCreate($org) {
    // Get Org Id
    $org_user_id = \Drupal::service('dmt_organisation.organisation')
        ->createOrganisation();

    $org_user = user_load($org_user_id);

    $address = $this->formatAddressField($org->address);

    $org_profile = Profile::create([
      'type' => 'organisation_profile',
      'uid' => $org_user_id,
      'field_org_title' => $org->name,
      'field_org_email' => [$org->mail],
      'field_org_address' => $address
    ]);

    $org_profile->save();

    return $org_user;
  }

  /**
   * Format Address Field
   *
   * @param $value
   * @return array
   */
  public function formatAddressField($value) {
    // example address:
    // 10000 Zagreb, Trg sv. Marka 6

    // $address_prep
    // [0] => 10000 Zagreb
    // [1] => Trg sv. Marka 6
    $address_prep = explode(', ', $value, 2);

    // $address_postal_code_and_city
    // [0] => 10000
    // [1] => Zagreb
    $address_postal_code_and_city = explode(" ", $address_prep[0], 2);

    $address = [
      'address_line1' => $address_prep[1],
      'postal_code' => $address_postal_code_and_city[0],
      'locality' => $address_postal_code_and_city[1],
      'country_code' => 'HR'
    ];

    return $address;
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
   * Log out.
   *
   * @Given I logout
   */
  public function iLogOut() {
    $page = '/user/logout';
    $this->visitPath($page);
  }

  /**
   * @When I close the error message
   */
  public function iCloseTheErrorMessage() {
    $locator = 'a.close';
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $locator);
    if ($element === NULL) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $locator));
    }
    // Now click the element.
    $element->click();
  }

}

