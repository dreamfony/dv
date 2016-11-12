<?php

namespace Drupal\dv_organisation_migrate\Plugin\migrate\process;

use Drupal\dv_organisation_migrate\ExtractTelephonesTrait;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "field_o_telephone"
 * )
 */
class FieldOTelephone extends ProcessPluginBase {

  use ExtractTelephonesTrait;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

      return self::extract($value, 'tel.:');

  }

}
