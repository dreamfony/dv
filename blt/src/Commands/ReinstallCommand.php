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

    // TODO refactor to custom:xdebug that excepts en and dis parameter
    /*
     * this is not a good way to solve this problem
    if(extension_loaded('xdebug')) {
      // disable xdebug in cli
      $this->taskExecStack()
        ->stopOnFail()
        ->exec('sudo phpdismod -s cli xdebug')
        ->exec('sudo service php7.0-fpm restart')
        ->run();
    }
    */

    $this->invokeCommand('custom:dbdump');

    $this->invokeCommand('setup');

    // clear caches
    $this->taskDrush()
      ->drush('cr')
      ->run();

    // clear caches
    $this->taskDrush()
      ->drush('fra')
      ->assume(TRUE)
      ->run();

    $this->invokeCommand('custom:import-content');

    // TODO refactor to custom:xdebug that excepts en and dis parameter
    // enable xdebug in cli
    /*
    if(!extension_loaded('xdebug')) {
      $this->taskExecStack()
        ->stopOnFail()
        ->exec('sudo phpenmod -s cli xdebug')
        ->exec('sudo service php7.0-fpm restart')
        ->run();
    }
    */

  }

}
