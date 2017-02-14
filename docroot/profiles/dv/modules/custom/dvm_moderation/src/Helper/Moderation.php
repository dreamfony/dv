<?php

namespace Drupal\dvm_moderation\Helper;

use Drupal\dvm_moderation\Helper\ModerationSettings;

/**
 * Class Moderation
 * @package Drupal\dvm_moderation\Helper
 */
final class Moderation {

  static function getModerationGroup(array $status) {

    $query = \Drupal::entityQuery('group');
    $query->condition('type', ModerationSettings::BUNDLE)
      ->condition('field_mg_event_type', $status, 'IN');
    $entity_ids = $query->execute();
    print_r($entity_ids);

  }

}
