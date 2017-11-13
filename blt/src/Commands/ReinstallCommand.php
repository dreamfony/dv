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

    if(extension_loaded('xdebug')) {
      // disable xdebug in cli
      $this->taskExecStack()
        ->stopOnFail()
        ->exec('sudo phpdismod -s cli xdebug')
        ->exec('sudo service php7.1-fpm restart')
        ->run();
    }

    // dump db in backup.sql
    $options = ['result-file' => 'db-backup-'.time().'.sql'];
    $drush = $this->taskDrush()->drush("sql-dump")
      ->options($options, '=');
    $result = $drush->run();
    $exit_code = $result->getExitCode();

    if($exit_code) {
      $this->say("Database not dumped! If it's your first time installation this is normal.");
    }

    $this->invokeCommand('setup');

    // clear caches
    $this->taskDrush()
      ->drush('cr')
      ->run();

    $this->invokeCommand('custom:import-content');

    // enable xdebug in cli
    if(!extension_loaded('xdebug')) {
      $this->taskExecStack()
        ->stopOnFail()
        ->exec('sudo phpenmod -s cli xdebug')
        ->exec('sudo service php7.1-fpm restart')
        ->run();
    }

  }

}
