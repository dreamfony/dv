<?php

namespace Drupal\geo_area_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "field_geo_area_parent"
 * )
 */
class FieldGeoAreaParent extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if ($value) {
      $query = \Drupal::entityQuery('node');

      $geoAreaParentGeoId = $row->getSourceProperty('field_geo_area_parent');

      $query->condition('type', 'geo_area');
      $query->condition('field_geo_area_geonames_id', $geoAreaParentGeoId );
      $result = $query->execute();

      if ($result) {
        return reset($result);
      }
    }

    return NULL;
  }

}
