<?php

namespace Drupal\geo_area\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "field_geo_area_admin_area_level"
 * )
 */
class FieldGeoAreaAdminAreaLevel extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if ($value) {
      preg_match_all('!\d+!', $value, $matches);

      if ($matches) {
        return reset($matches);
      }
    }

    return NULL;
  }

}
