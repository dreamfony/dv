<?php

namespace Drupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Environment\Environment;
use Drupal\DrupalExtension\Hook\Scope\EntityScope;


/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class PersonaContext extends RawDrupalContext implements SnippetAcceptingContext {

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
        'personas' => array($persona),
      );
      $user->mail = "{$user->name}@example.com";

      $user = $this->userCreate($user);

      // Login.
      $this->login($user);
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
    $user = $this->getUserManager()->getCurrentUser();
    return $this->loggedIn() && $user && isset($user->personas) && in_array($persona, $user->personas);
  }


}

