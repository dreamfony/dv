<?php

namespace Drupal\hr_organisation_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "field_o_address"
 * )
 */
class FieldOAddress extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if($value) {

      // example address:
      // 10000 Zagreb, Trg sv. Marka 6

      // $address_prep
      // [0] => 10000 Zagreb
      // [1] => Trg sv. Marka 6
      $address_prep = explode(', ', $value, 2);

      // $address_postal_code_and_city
      // [0] => 10000
      // [1] => Zagreb
      $address_postal_code_and_city = explode(" ", $address_prep[0], 2);

      $address = [
        'organisation' => $row->getSourceProperty('organisation'),
        'address_line1' => $address_prep[1],
        'postal_code' => $address_postal_code_and_city[0],
        'locality' => $address_postal_code_and_city[1],
        'country_code' => 'HR'
      ];

      return $address;
    }

  }

}
