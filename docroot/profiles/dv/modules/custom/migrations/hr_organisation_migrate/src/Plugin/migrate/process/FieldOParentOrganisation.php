<?php

namespace Drupal\hr_organisation_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "field_o_parent_organisation"
 * )
 */
class FieldOParentOrganisation extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if ($value) {
      $query = \Drupal::entityQuery('node');

      $query->condition('type', 'organisation');
      $query->condition('field_o_organisation_id', $value);
      $result = $query->execute();

      if ($result) {
        return reset($result);
      }
    }

    return NULL;
  }

}
