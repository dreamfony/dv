<?php

namespace Drupal\hr_organisation_migrate\Plugin\migrate\process;

use Drupal\hr_organisation_migrate\ExtractTelephonesTrait;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "field_org_fax"
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
