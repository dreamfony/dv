<?php

namespace Drupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Drupal\dmt_core\PersonaAccountUtility;

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

}
