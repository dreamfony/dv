<?php

namespace Drupal\dv_organisation_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "field_o_organisation_group"
 * )
 */
class FieldOOrganisationGroup extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if ($value) {
      $query = \Drupal::entityQuery('taxonomy_term');

      $query->condition('vid', 'organisation_group');
      $query->condition('field_organisation_group_id', $value);
      $result = $query->execute();

      if ($result) {
        return reset($result);
      }
    }

    return NULL;
  }

}
