<?php

namespace Drupal\dvm_organisation\Helper;

use Drupal\dvm_user\Helper\UserSettings;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Class Organisation
 * @package Drupal\dvm_organisation\Helper
 */
class Organisation extends Node {

  public function getOrganisationUserEntity() {

    $query = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('roles', UserSettings::ROLE_ORGANISATION)
      ->condition('field_ac_organisation_node', $this->id());
    $entity_id = $query->execute();

    if (!empty($entity_id)) {
      $user = User::load(reset($entity_id));
      return $user;
    }
    return NULL;
  }
}
