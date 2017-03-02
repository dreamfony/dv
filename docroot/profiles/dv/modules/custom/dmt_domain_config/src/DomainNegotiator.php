<?php

namespace Drupal\dmt_domain_config;

use Drupal\domain\DomainNegotiator as DomainDomainNegotiator;

/**
 * {@inheritdoc}
 */
class DomainNegotiator extends DomainDomainNegotiator {

  public function isActiveDomainDefault() {
    if(isset($this->domain)) {
      return $this->domain->isDefault();
    }
  }
}
