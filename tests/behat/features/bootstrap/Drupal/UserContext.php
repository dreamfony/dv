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
class UserContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * @var \Drupal\DrupalExtension\Context\DrupalContext
   */
  protected $drupalContext;

  /**
   * @var \Drupal\FeatureContext
   */
  protected $featureContext;

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
    $this->featureContext = $environment->getContext('Drupal\FeatureContext');
  }

  /**
   * @AfterScenario @user
   */
  public function cleanupUsers(AfterScenarioScope $scope) {
    $this->featureContext->cleanUp('user','name', 'Test', FALSE);
  }

  /**
   * Hook into user creation to add profile fields `@afterUserCreate`
   *
   * @afterUserCreate
   */
  public function alterUserParameters(EntityScope $event) {
    /*
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
    */
  }

}

