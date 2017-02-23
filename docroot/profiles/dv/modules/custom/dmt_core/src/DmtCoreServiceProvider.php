<?php

namespace Drupal\dmt_core;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Defines a service for the dmt_core module.
 */
class DmtCoreServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Replace the regular config.factory service with a traceable one.
    $container->getDefinition('config.factory')
      ->setClass('Drupal\dmt_core\Config\ConfigFactoryWrapper');
  }
}
