<?php

namespace Drupal\dmt_core\Config;

use Drupal\Core\Config\ConfigFactory;


/**
 * Wraps a config factory to be able to figure out all used config files.
 */
class ConfigFactoryWrapper extends ConfigFactory {

  /**
   * {@inheritdoc}
   */
  public function getEditable($name) {

    /** @var \Drupal\domain\Entity\Domain $active */
    $active = \Drupal::service('domain.negotiator')->getActiveDomain();
    if ($active->isDefault()) {
      return $this->doGet($name, FALSE);
    } else {
      $mutable = $this->doGet($name, FALSE);
      $overrides = $this->loadOverrides([$name]);

      if(isset($overrides[$name])) {
        $overrides = $overrides[$name];
        foreach ($overrides as $key => $value) {
          $mutable->set($key, $value);
        }
      }
    }

    return $mutable;
  }
}
