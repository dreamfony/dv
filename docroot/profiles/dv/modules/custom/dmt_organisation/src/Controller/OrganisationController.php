<?php

namespace Drupal\dmt_organisation\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class CreateOrganisation.
 *
 * @package Drupal\dmt_organisation\Controller
 */
class OrganisationController extends ControllerBase {

  /**
   * Create Organisation.
   *
   * @return string
   */
  public function createOrganisation() {
    $organisation_id = Drupal::service('dmt_organisation.organisation')
      ->getOrganisationId();

    $dummy_email = Drupal::service('dmt_organisation.organisation')
      ->getOrganisationDummyEmail($organisation_id);

    $user = Drupal::service('dmt_organisation.organisation')
      ->createOrganisationUser($dummy_email);

    $profile = Profile::create([
      'type' => 'organisation_profile',
      'uid' => $user->id(),
    ]);
    $profile->set('field_org_organisation_id', $organisation_id);
    $status = $profile->save();

    // Redirect to user/$user_id/organisation_profile
    return $this->redirect('entity.group.canonical', ['group' => $user->id()]);
  }
}
