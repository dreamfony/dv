<?php

namespace Drupal\moderation_state_machine;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the language manager service.
 */
class ModerationStateMachineServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides language_manager class to test domain language negotiation.
    $definition = $container->getDefinition('content_moderation.state_transition_validation');
    $definition->setClass('Drupal\moderation_state_machine\StateTransitionValidation');
  }
}
