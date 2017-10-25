<?php

namespace Site;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Environment\Environment;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Drupal\field_group\Plugin\field_group\FieldGroupFormatter\Tab;
use Drupal\profile\Entity\Profile;
use Drupal\group\Entity\Group;
use Drupal\DrupalExtension\Hook\Scope\EntityScope;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class SurveyContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * @var \Drupal\DrupalExtension\Context\DrupalContext
   */
  protected $drupalContext;

  /**
   * @var \Drupal\GroupContext
   */
  protected $groupContext;

  /**
   * @var \Drupal\user\Entity\User
   */
  protected $surveyUser;

  /**
   * @var Group
   */
  protected $survey;

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
    $this->groupContext = $environment->getContext('Drupal\GroupContext');
  }


  /**
   * Creates test survey
   *
   * @Given user :user_name has survey :survey_title
   */
  public function createSurvey($userName, $surveyTitle) {

    $user = (object) array(
      'name' => $userName,
      'pass' => $this->getRandom()->name(16),
      'personas' => array('journalist'),
    );
    $user->mail = "{$user->name}@example.com";

    $this->surveyUser = user_load($this->userCreate($user)->uid);

    $survey_data =  (object) [
      'language' => 'en',
      'title' => $surveyTitle,
      'type' => 'mailing_list',
      'uid' => $this->surveyUser->id()
    ];

    $this->groupContext->groupCreate($survey_data);
  }

  /**
   * Creates survey content:
   * | body        | answer_format   |
   * | Test Test   | text            |
   * | ...         | ...             |
   *
   * @Given :survey_title survey has content:
   */
  public function createSurveyContent($surveyTitle, TableNode $cTable) {
    foreach ($cTable->getHash() as $cHash) {
      $cObj = (object) $cHash;

      // get survey
      $survey = $this->groupContext->loadGroupByLabelAndType($surveyTitle, 'mailing_list');

      $node = (object) array(
        'type' => 'content',
        'body' => $cObj->body,
        'uid' => $survey->getOwnerId(),
        'field_answer_format' => $cObj->answer_format
      );

      $saved = $this->nodeCreate($node);

      // add content to survey
      $survey->addContent($saved, 'group_node:' . $saved->bundle());
    }
  }

  /**
   * Add recipients to survey:
   * | name        |
   * | Test Test   |
   * | ...         |
   *
   * @Given :survey_title survey has recipients:
   */
  public function addSurveyRecipients($surveyTitle, TableNode $rTable) {
    // find organisation users with names
    // add organisation user to mailing list
    foreach ($rTable->getHash() as $r) {
      $recipients = (object) $r;

      $recipient = \Drupal::entityTypeManager()->getStorage('user')
        ->loadByProperties(['name' => $recipients->name]);

      // get survey
      $survey = $this->groupContext->loadGroupByLabelAndType($surveyTitle, 'mailing_list');

      $survey->addMember($recipient, ['group_roles' => ['mailing_list-organisation']]);
    }
  }

}

