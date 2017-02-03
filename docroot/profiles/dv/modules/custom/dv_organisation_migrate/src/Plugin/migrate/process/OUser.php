<?php

namespace Drupal\dv_organisation_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\user\Entity\User;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "o_user"
 * )
 */
class OUser extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if ($row->getSourceProperty('komunikacije')) {
      $email = $this->parseEmailFromKomunikacije($row->getSourceProperty('komunikacije'));
    }

    if (!isset($email)) {
      $email = $this->generateEmail($row->getSourceProperty('organisation_id'));
    }

    $org_user = user_load_by_mail($email);

    if (empty($org_user)) {
      $org_user = User::create(array(
        'name' => $email,
        'mail' => $email,
        'status' => 1,
        'roles' => array('organisation')
      ));
      $org_user->save();
    }

    return $org_user->id();

  }

  protected function parseEmailFromKomunikacije($komunikacije) {
    $matches = [];
    $pattern = '/[a-z\d._%+-]+@[a-z\d.-]+\.[a-z]{2,4}\b/i';
    preg_match_all($pattern, $komunikacije, $matches);

    if ($matches) {
      return reset($matches)[0];
    }


    return NULL;
  }

  protected function generateEmail($organisationId) {
    return $organisationId . '@' . 'no-email.com';
  }

}
