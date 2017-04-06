<?php

namespace Drupal\dmt_organisation\Helper;

use Drupal\profile\Entity\Profile;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\dvm_user\Helper\UserSettings;

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

  public function getOrganisationIdByUser(UserInterface $user) {
//    get users profile field id query if empty then
    $profile = \Drupal::entityManager()->getStorage('profile')
      ->loadByUser($user, UserSettings::PROFILE_ORGANISATION);

    if ($profile) {
      $organisation_id = $profile->get('field_org_organisation_id')->getValue();
      if (empty($organisation_id)) {
        $organisation_id = $this->getOrganisationIdByUserFromEmail($user);
      }
    }
    else {
      $organisation_id = $this->getOrganisationIdByUserFromEmail($user);
    }

    return $organisation_id;
  }

  private function getOrganisationIdByUserFromEmail(UserInterface $user) {
    $email = $user->getEmail();
    $exploded_mail = explode('@', $email);
    return $exploded_mail[0];
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
    $user = user_load_by_mail($this->getOrganisationDummyEmail($id));
    return $user ? TRUE : FALSE;
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

    return $user->id();
  }

  public function getRelatedOrganisationGroupId(Profile $profile) {
//    TODO check if field is not empty
    return $profile->field_org_related_group->target_id;
  }
}
