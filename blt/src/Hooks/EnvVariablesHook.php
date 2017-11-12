<?php

namespace Acquia\Blt\Custom\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * This class defines example hooks.
 */
class EnvVariablesHook extends BltTasks {

  /**
   * This will be called before the `install-alias` command is executed.
   *
   * @hook command-event install-alias
   */
  public function preInstallAlias(ConsoleCommandEvent $event) {
    $command = $event->getCommand();
    $this->say("preInstallAlias hook: The {$command->getName()} command is about to run!");
    $_ENV['SHELL'] = 'zsh';
  }

}
