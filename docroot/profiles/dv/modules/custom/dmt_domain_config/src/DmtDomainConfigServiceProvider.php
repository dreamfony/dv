<?php

namespace Drupal\dmt_domain_config;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Defines a service for the dmt_domain_config module.
 */
class DmtDomainConfigServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Replace the regular domain.negotiator service.
    $container->getDefinition('domain.negotiator')
      ->setClass('Drupal\dmt_domain_config\DomainNegotiator');
  }
}
