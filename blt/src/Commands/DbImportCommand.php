<?php

namespace Acquia\Blt\Custom\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Defines commands in the "custom" namespace.
 */
class DbImportCommand extends BltTasks {

  /**
   * Db Dump Import Command. Imports latest dump from db_backup dir.
   *
   * @command custom:dbimport
   *
   *
   * @executeInDrupalVm
   */
  public function dbImport() {

    // check if we have any available db dumps
    $latest_db_dump = $this->getLatestFileName();

    if(!$latest_db_dump) {
      $this->say("No db dumps exist in db_backup directory.");
      return;
    }

    // drop db before import
    $drush = $this->taskDrush()->drush("sql-drop")
      ->assume(TRUE);
    $result = $drush->run();
    $exit_code = $result->getExitCode();

    if($exit_code) {
      $this->say("Database was not dropped!");
    } else {
      $this->say("Old database was dropped!");
    }

    $this->say("Importing db from: " . $latest_db_dump);

    // import latest db dump
    $drush = $this->taskDrush()->drush("sql-cli")
      ->rawArg('< '. $latest_db_dump);
    $result = $drush->run();
    $exit_code = $result->getExitCode();

    if($exit_code) {
      $this->say("Database did not import!");
    }
  }


  public function getLatestFileName() {
    $path = $this->getConfigValue('repo.root') . "/db_backup";

    $latest_ctime = 0;
    $latest_filename = false;

    $d = dir($path);
    while (false !== ($entry = $d->read())) {
      $filepath = "{$path}/{$entry}";
      $path_parts = pathinfo($filepath);

      if($path_parts['extension'] != 'sql') {
        continue;
      }

      // could do also other checks than just checking whether the entry is a file
      if (is_file($filepath) && filectime($filepath) > $latest_ctime) {
        $latest_ctime = filectime($filepath);
        $latest_filename = $entry;
      }
    }

    if(!$latest_filename) {
      return false;
    }

    return $path . '/' . $latest_filename;
  }

}
