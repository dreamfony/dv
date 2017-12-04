<?php

namespace Acquia\Blt\Custom\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Defines commands in the "custom" namespace.
 */
class DbDumpCommand extends BltTasks {

  /**
   * Db Dump Command.
   *
   * @command custom:dbdump
   *
   *
   * @executeInDrupalVm
   */
  public function db_dump() {

    // dump db in backup.sql
    $options = ['result-file' => $this->getConfigValue('repo.root') . '/db_backup/db-backup-'.time().'.sql'];
    $drush = $this->taskDrush()->drush("sql-dump")
      ->options($options, '=');
    $result = $drush->run();
    $exit_code = $result->getExitCode();

    if($exit_code) {
      $this->say("Database not dumped! If it's your first time installation this is normal.");
    }

  }

}
