<?php

namespace Acquia\Blt\Custom\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Defines commands in the "custom" namespace.
 */
class ReinstallCommand extends BltTasks {

  /**
   * Reinstall.
   *
   * @command custom:reinstall
   *
   * @option environment The environment key for which modules should be
   *   toggled. This should correspond with a import-content.[environment].* key in
   *   your configuration.
   *
   * @executeInDrupalVm
   */
  public function reinstall() {

    // dump db in backup.sql
    $options = ['result-file' => 'db-backup-'.time().'.sql'];
    $drush = $this->taskDrush()->drush("sql-dump")
      ->options($options, '=');
    $result = $drush->run();
    $exit_code = $result->getExitCode();

    if($exit_code) {
      throw new BltException("Could not dump the database.");
    }

    $this->invokeCommand('setup');
    $this->invokeCommand('custom:import-content');

  }

}
