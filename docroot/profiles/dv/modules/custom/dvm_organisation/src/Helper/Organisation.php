<?php

namespace Drupal\dvm_organisation\Helper;

use Drupal\dvm_user\Helper\UserSettings;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\Entity;

/**
 * Class Organisation
 * @package Drupal\dvm_organisation\Helper
 */
class Organisation {

  public function getOrganisationUserEntity(Entity $entity) {

    $query = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('roles', UserSettings::ROLE_ORGANISATION)
      ->condition('field_ac_organisation_node', $entity->id());
    $entity_id = $query->execute();

    if (!empty($entity_id)) {
      $user = User::load(reset($entity_id));
      return $user;
    }
    return NULL;
  }
}
