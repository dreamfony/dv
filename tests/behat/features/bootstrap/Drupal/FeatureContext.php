<?php

namespace Drupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Environment\Environment;

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

}

