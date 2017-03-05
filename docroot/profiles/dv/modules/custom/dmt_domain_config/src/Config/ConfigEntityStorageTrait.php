<?php

namespace Drupal\dmt_domain_config\Config;

trait ConfigEntityStorageTrait {

  /**
   * {@inheritdoc}
   */
  public function loadMultipleOverrideFree(array $ids = NULL) {
    $entities = $this->loadMultiple($ids);
    return $entities;
  }

}
