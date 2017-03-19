<?php

namespace Drupal\dvm_organisation\Helper;

use Drupal\profile\Entity\Profile;

/**
 * Class Organisation
 * @package Drupal\dvm_organisation\Helper
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

  private function checkOrganisationIdIsUsed($id) {
    $count_id = \Drupal::entityQuery('profile')
      ->condition('type', 'organisation_profile')
      ->condition('field_org_organisation_id', $id)
      ->count()
      ->execute();

    return $count_id > 0;
  }
}
