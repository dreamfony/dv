<?php

namespace Acquia\Blt\Custom\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Console\Input\InputOption;
use Acquia\Blt\Robo\Exceptions\BltException;


/**
 * This class defines example hooks.
 */
class ToggleModulesHook extends BltTasks {

  /**
   * This will be called before the `custom:hello` command is executed.
   *
   * @hook replace-command setup:toggle-modules
   */
  public function toggleModules($options = [
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
      // Enable modules.
      $enable_key = "modules.$environment.enable";
      $this->doToggleModules('pm-enable', $enable_key);

      // Uninstall modules.
      $disable_key = "modules.$environment.uninstall";
      $this->doToggleModules('pm-uninstall', $disable_key);
    }
    else {
      $this->say("Environment is unset. Skipping setup:toggle-modules...");
    }
  }

  /**
   * Enables or uninstalls an array of modules.
   *
   * @param string $command
   *   The drush command to execute. E.g., pm-enable or pm-uninstall.
   * @param string $config_key
   *   The config key containing the array of modules.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function doToggleModules($command, $config_key) {
    if ($this->getConfig()->has($config_key)) {
      $modules = $this->getConfigValue($config_key);

      foreach ($modules as $module) {
        $result = $this->taskDrush()
          ->drush("$command $module")
          ->assume(TRUE)
          ->run();
        $exit_code = $result->getExitCode();

        if ($exit_code) {
          throw new BltException("$module riknio.");
        }
      }

    }
    else {
      $exit_code = 0;
      $this->logger->info("$config_key is not set.");
    }

    if ($exit_code) {
      throw new BltException("Could not toggle modules listed in $config_key.");
    }
  }


}
