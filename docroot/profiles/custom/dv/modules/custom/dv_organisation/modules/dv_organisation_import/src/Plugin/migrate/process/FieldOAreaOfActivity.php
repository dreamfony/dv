<?php

namespace Drupal\dv_organisations_import\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "field_o_area_of_activity"
 * )
 */
class FieldOAreaOfActivity extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if ($value) {
      $query = \Drupal::entityQuery('taxonomy_term');

      $query->condition('vid', 'area_of_activity');
      $query->condition('field_area_of_activity_id', $value);
      $result = $query->execute();

      if ($result) {
        return reset($result);
      }
    }

    return NULL;
  }

}
