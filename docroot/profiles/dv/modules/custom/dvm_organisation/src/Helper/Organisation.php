<?php

namespace Drupal\dvm_organisation\Helper;

use Drupal\Core\Entity\Entity;
use Drupal\dvm_user\Helper\UserSettings;
use Drupal\user\Entity\User;

/**
 * Class Organisation
 * @package Drupal\dvm_organisation\Helper
 */
final class Organisation {

  static function getOrganisationUserEntity(Entity $entity) {

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
