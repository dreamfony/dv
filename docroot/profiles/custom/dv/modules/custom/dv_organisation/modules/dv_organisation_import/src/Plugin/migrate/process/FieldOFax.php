<?php

namespace Drupal\dv_organisations_import\Plugin\migrate\process;

use Drupal\import_organisations\ExtractTelephonesTrait;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "field_o_fax"
 * )
 */
class FieldOFax extends ProcessPluginBase {

  use ExtractTelephonesTrait;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    return self::extract($value, 'faks:');
  }

}
