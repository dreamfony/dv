<?php

namespace Site;

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
class OrganisationContext extends RawDrupalContext implements SnippetAcceptingContext {

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
   * Creates organisation:
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

    $activity_group = $this->groupContext->groupCreate($group);

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

}

