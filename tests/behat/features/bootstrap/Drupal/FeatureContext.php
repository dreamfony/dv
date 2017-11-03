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
   * @var string
   */
  private $url;


  /**
   * Every scenario gets its own context instance.
   *
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {

  }

  /**
   * @BeforeScenario @javascript
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
   * @Given I wait for :seconds second/seconds
   */
  public function iWaitForXSeconds($seconds) {
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
    $region = $this->minkContext->getRegion($region);
    $this->assertSession()->fieldValueEquals($field, $value, $region);
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

  /**
   * @When I remember the current url
   */
  public function iRememberCurrentUrl() {
    $session = $this->getSession();
    $this->url = $session->getCurrentUrl();
  }

  /**
   * @When I check that the url is the same as I remembered it
   */
  public function iCheckThatUrlIsTheSame() {
    $session = $this->getSession();
    if($this->url != $session->getCurrentUrl()) {
      throw new \Exception('Current url is not the same as before');
    }
  }

  /**
   * @When I wait for the queue to be empty
   */
  public function iWaitForTheQueueToBeEmpty() {
    $workerManager = \Drupal::service('plugin.manager.queue_worker');
    /** @var QueueFactory $queue */
    $queue = \Drupal::service('queue');
    for ($i = 0; $i < 20; $i++) {
      foreach ($workerManager->getDefinitions() as $name => $info) {
        /** @var QueueInterface $worker */
        $worker = $queue->get($name);
        /** @var \Drupal\Core\Queue\QueueWorkerInterface $queue_worker */
        $queue_worker = $workerManager->createInstance($name);
        if ($worker->numberOfItems() > 0) {
          while ($item = $worker->claimItem()) {
            $queue_worker->processItem($item->data);
            $worker->deleteItem($item);
          }
        }
      }
    }
  }

  /**
   * Cleanup data after scenario.
   */
  public function cleanUp($entityType, $fieldName, $fieldValue, $isArray = TRUE) {
    $query = \Drupal::entityQuery($entityType);

    if($isArray) {
      $query->condition($fieldName, array(
        $fieldValue . ' %'
      ), 'LIKE');
    }

    $query->condition($fieldName,$fieldValue . ' %', 'LIKE');

    $ids = $query->execute();

    $entities = \Drupal::entityTypeManager()->getStorage($entityType)
      ->loadMultiple($ids);

    foreach ($entities as $entity) {
      $entity->delete();
    }
  }

}

