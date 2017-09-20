<?php

namespace Acquia\Blt\Custom\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Defines commands in the "custom" namespace.
 */
class ImportContentCommand extends BltTasks {

  /**
   * Imports content.
   *
   * You may define the environment for which modules should be toggled by
   * passing the --environment=[value] option to this command setting
   * $_ENV['environment'] via the CLI, or defining environment in one of your
   * BLT configuration files.
   *
   * @command custom:import-content
   *
   * @option environment The environment key for which modules should be
   *   toggled. This should correspond with a import-content.[environment].* key in
   *   your configuration.
   *
   * @executeInDrupalVm
   */
  public function importContent($options = [
    'environment' => InputOption::VALUE_REQUIRED,
  ]) {
    if ($options['environment']) {
      $environment = $options['environment'];
    }
    elseif ($this->getConfig()->has('environment')) {
      $environment = $this->getConfigValue('environment');
    }
    elseif (!empty($_ENV['environment'])) {
      $environment = $_ENV['environment'];
    }

    if (isset($environment)) {
      // Migrate.
      $migrate = "import-content.$environment.migrate";
      $this->doImport($migrate, 'mi');

      // Custom.
      $custom = "import-content.$environment.custom";
      $this->doImport($custom);
    }
    else {
      $this->say("Environment is unset. Skipping custom:import-content...");
    }
  }

  /**
   * Do import.
   *
   * @param $import
   * @param string $command
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function doImport($import, $command = '') {
    if ($this->getConfig()->has($import)) {
      $imports = $this->getConfigValue($import);
      foreach ($imports as $name => $vars) {
        if($vars['import'] === TRUE) {
          $drush = $this->taskDrush()->drush("$command $name");
          if(isset($vars['options'])) {
            $drush->options($vars['options'], '=');
          }
          $result = $drush->run();
        }
        $exit_code = $result->getExitCode();
      }
    }
    else {
      $exit_code = 0;
      $this->logger->info("$import is not set.");
    }

    if ($exit_code) {
      throw new BltException("Could import content listed in $import.");
    }
  }

}
