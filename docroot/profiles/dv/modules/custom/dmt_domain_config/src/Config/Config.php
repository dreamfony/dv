<?php

namespace Drupal\dmt_domain_config\Config;

use Drupal\Core\Config\Config as CoreConfig;
use Drupal\domain\DomainNegotiatorInterface;

/**
 * Extend core Config class to save domain specific configuration.
 */
class Config extends CoreConfig {
  /**
   * List of config that should always be saved globally.
   */
  const GLOBAL_CONFIG = [
    'core.extension',
  ];

  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   *
   */
  protected $activeDomainDefault;

  /**
   * Set the Domain negotiator.
   * @param DomainNegotiatorInterface $domain_negotiator
   */
  public function setDomainNegotiator(DomainNegotiatorInterface $domain_negotiator) {
    $this->domainNegotiator = $domain_negotiator;
  }

  /**
   * {@inheritdoc}
   */
  public function save($has_trusted_data = FALSE) {
    // Remember original config name.
    $originalName = $this->name;

    try {
      // Get domain config name for saving.
      $domainConfigName = $this->getDomainConfigName();

      // If config is new and we are currently saving domain specific configuration,
      // save with original name first so that there is always a default configuration.
      if (($this->isNew) || $originalName == $domainConfigName) {
        parent::save($has_trusted_data);
      }

      if ($domainConfigName != $originalName) {
        $this->setDomainConfigData();

        if(!empty($this->data)) {
          // Switch to use domain config name and save.
          $this->name = $domainConfigName;
          parent::save($has_trusted_data);
        }
      }
    }
    catch (\Exception $e) {
      // Reset back to original config name if save fails and re-throw.
      $this->name = $originalName;
      throw $e;
    }

    // Reset back to original config name after saving.
    $this->name = $originalName;

    return $this;
  }

  protected function getOriginalData() {
    /** @var \Drupal\Core\Config\Config $original */
    $original = \Drupal::service('config.factory')
      ->getEditable($this->name);
    return $this->getOriginal();
  }

  /**
   * Config diff.
   *
   * @return array
   */
  protected function setDomainConfigData() {
    $diff = [];

    $original_data = $this->getOriginalData();

    foreach ($original_data as $key => $value) {
      $new_data_value = json_encode($this->data[$key]);
      $original_data_value = json_encode($value);

      if($new_data_value != $original_data_value) {
        $diff[$key] = $this->data[$key];
      }

      if(isset($this->data[$key])) {
        unset($this->data[$key]);
      }
    }

    $this->data = $this->data + $diff;
  }

  /**
   * Get the domain config name.
   */
  protected function getDomainConfigName() {
    // Get default global config and allow other modules to alter.
    $global_config = self::GLOBAL_CONFIG;
    \Drupal::moduleHandler()->alter('dmt_domain_config_global_config', $global_config);

    // Return original name if reserved as global configuration.
    if (in_array($this->name, $global_config)) {
      return $this->name;
    }

    // Build prefix and add to front of existing key.
    if(!$this->activeDomainDefault) {
      if ($selected_domain = $this->domainNegotiator->getActiveDomain()) {
        $prefix = 'domain.config.' . $selected_domain->id() . '.';
        if ($language = \Drupal::languageManager()->getCurrentLanguage()) {
          $prefix .= $language->getId() . '.';
        }
        return $prefix . $this->name;
      }
    }

    // Return current name by default.
    return $this->name;
  }

  public function isActiveDomainDefault($active) {
    $this->activeDomainDefault = $active;
  }
}
