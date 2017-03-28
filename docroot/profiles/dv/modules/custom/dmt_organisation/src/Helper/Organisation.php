<?php

namespace Drupal\dmt_organisation\Helper;

use Drupal\profile\Entity\Profile;
use Drupal\user\Entity\User;

/**
 * Class Organisation
 * @package Drupal\dmt_organisation\Helper
 */
class Organisation {

  /**
   * @param \Drupal\profile\Entity\Profile $entity
   * @return \Drupal\Core\Entity\EntityInterface|null|static
   */
  public function getOrganisationUserEntity(Profile $entity) {
    return $entity->getOwner();
  }

  /**
   * Generate Unique Organisation ID
   */
  public function getOrganisationId() {
    $generated_id = $this->generateOrganisationId();
    while ($this->checkOrganisationIdIsUsed($generated_id)) {
      $generated_id = $this->generateOrganisationId();
    }
    return $generated_id;
  }

  private function generateOrganisationId() {
    return rand(10000000, 99999999);
  }

  public function checkOrganisationIdIsUsed($id) {
    $count_id = \Drupal::entityQuery('profile')
      ->condition('type', 'organisation_profile')
      ->condition('field_org_organisation_id', $id)
      ->count()
      ->execute();

    return $count_id > 0;
  }

  public function getOrganisationDummyEmail($organisation_id) {
//    TODO make dv.com configurable
    return $organisation_id . '@dv.com';
  }

  /**
   * @param $email
   * @return mixed
   */
  public function createOrganisationUser($email) {
    $user = User::create(array(
      'name' => $email,
      'mail' => $email,
      'status' => 1,
      'personas' => array('organisation')
    ));
    $user->save();
    return $user;
  }


  /**
   * Create Organisation.
   *
   * @return string
   */
  public function createOrganisation() {

    $organisation_id = $this->getOrganisationId();
    $dummy_email = $this->getOrganisationDummyEmail($organisation_id);
    $user = $this->createOrganisationUser($dummy_email);

    $profile = Profile::create([
      'type' => 'organisation_profile',
      'uid' => $user->id(),
    ]);

    $profile->set('field_org_organisation_id', $organisation_id);
    $profile->save();

    return $user->id();
  }
}
