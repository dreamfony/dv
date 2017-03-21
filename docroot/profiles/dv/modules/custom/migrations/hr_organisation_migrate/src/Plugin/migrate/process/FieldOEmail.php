<?php

namespace Drupal\hr_organisation_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "field_org_email"
 * )
 */
class FieldOEmail extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if ($row->getSourceProperty('komunikacije')) {
      $emails = $this->parseEmailFromKomunikacije($row->getSourceProperty('komunikacije'));
    }

    if (!isset($emails)) {
      $emails[] = $this->generateEmail($row->getSourceProperty('organisation_id'));
    }

    return $emails;

  }

  protected function parseEmailFromKomunikacije($komunikacije) {
    $matches = [];
    $pattern = '/[a-z\d._%+-]+@[a-z\d.-]+\.[a-z]{2,4}\b/i';
    preg_match_all($pattern, $komunikacije, $matches);

    if ($matches) {
      return reset($matches);
    }

    return NULL;
  }

  protected function generateEmail($organisationId) {
    return $organisationId . '@' . 'no-email.com';
  }

}
