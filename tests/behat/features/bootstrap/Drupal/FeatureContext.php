<?php

namespace Drupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Drupal\dmt_core\PersonaAccountUtility;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Environment\Environment;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Every scenario gets its own context instance.
   *
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {

  }

  /**
   * Gives us acesss to the other contexts so we can access their properties.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    /** @var Environment $environment */
    $environment = $scope->getEnvironment();

    $this->contexts['drupal'] = $environment->getContext('Drupal\DrupalExtension\Context\DrupalContext');
    $this->contexts['mink'] = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
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
    $persona_utility = new PersonaAccountUtility();
    return $this->loggedIn() && $this->user && isset($this->user->personas) && $persona_utility::hasPersona($this->user, 'journalist');
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
    $this->contexts['mink']->iWaitForAjaxToFinish();
    // Select the first option.
    $driver->keyDown($xpath, 40);
    $driver->keyUp($xpath, 40);
    // Press the Enter key to confirm selection, copying the value into the field.
    $driver->keyDown($xpath, 13);
    $driver->keyUp($xpath, 13);
    $this->contexts['mink']->iWaitForAjaxToFinish();
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

}
