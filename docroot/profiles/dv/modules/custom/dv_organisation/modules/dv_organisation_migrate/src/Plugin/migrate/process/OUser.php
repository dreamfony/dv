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

    /// what if more organisations have same email?
    /////// link nodes to same email
    /// what if email does not exist?
    /////// generate random email address set as some flag
    /// how to handle mail confirmation?
    /////// second flag?

    if ($row->getSourceProperty('komunikacije')) {

    }
    $user_exists = user_load_by_mail($mail);
    if (empty($user_exists)) {
      $user = User::create(array(
        'name' => $value,
        'mail' => $mail,
        'status' => 1,
        'roles' => array('Organisation')
      ));
      $user->save();
    }

  }

  protected function parseEmailFromKomunikacije() {
    if ($value) {

      $matches = [];
      $pattern = '/[a-z\d._%+-]+@[a-z\d.-]+\.[a-z]{2,4}\b/i';
      preg_match_all($pattern, $value, $matches);

      if ($matches) {
        return reset($matches)[0];
      }
    }

    return NULL;
  }

  protected function generateEmail() {

  }

}
