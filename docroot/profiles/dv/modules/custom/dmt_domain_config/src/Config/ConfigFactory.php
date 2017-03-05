<?php
namespace Drupal\dmt_domain_config\Config;

use Drupal\Core\Config\ConfigFactory as CoreConfigFactory;
use Drupal\domain\DomainNegotiatorInterface;
/**
 * Overrides Drupal\Core\Config\ConfigFactory in order to use our own Config class.
 */
class ConfigFactory extends CoreConfigFactory {
  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiator
   */
  protected $domainNegotiator;

  protected $activeDomainDefault = TRUE;

  /**
   * {@inheritDoc}
   * @see \Drupal\Core\Config\ConfigFactory::createConfigObject()
   */
  protected function createConfigObject($name, $immutable) {
    if (!$immutable) {
      $config = new Config($name, $this->storage, $this->eventDispatcher, $this->typedConfigManager);
      // Pass the negotiator to the Config object.
      $config->setDomainNegotiator($this->domainNegotiator);
      $config->isActiveDomainDefault($this->isActiveDomainDefault());
      return $config;
    }
    return parent::createConfigObject($name, $immutable);
  }

  /**
   * Set the Domain negotiator.
   * @param DomainNegotiatorInterface $domain_negotiator
   */
  public function setDomainNegotiator(DomainNegotiatorInterface $domain_negotiator) {
    $this->domainNegotiator = $domain_negotiator;
    $this->activeDomainDefault = $this->isActiveDomainDefault();
  }

  public function isActiveDomainDefault() {
    // if we have active domain in cache
    if($active = $this->domainNegotiator->getActiveDomain()) {
      return $active->isDefault();
    }

    // check if we are in cli (drush) if so skip this to avoid circular reference
    if(PHP_SAPI !== 'cli') {
      return TRUE;
    }

    // if active domain is null try to reset domain negotiator cache
    if($active = $this->domainNegotiator->getActiveDomain(TRUE)) {
      return $active->isDefault();
    }

    // if all else fails return true since this may be drush request
    return TRUE;
  }

  /**
   * {@inheritDoc}
   * @see \Drupal\Core\Config\ConfigFactory::doLoadMultiple()
   */
  protected function doLoadMultiple(array $names, $immutable = TRUE) {
    // Let parent load multiple load as usual.
    $list = parent::doLoadMultiple($names, $immutable);

    // Pre-load remaining configuration files.
    if (!empty($names)) {
      // Initialise override information.
      $module_overrides = array();
      $storage_data = $this->storage->readMultiple($names);
      // Load module overrides so that domain specific config is loaded in admin forms.
      if (!empty($storage_data)) {
        // Only get module overrides if we have configuration to override.
        $module_overrides = $this->loadOverrides($names);
      }
      foreach ($storage_data as $name => $data) {
        $cache_key = $this->getConfigCacheKey($name, $immutable);
        if (isset($module_overrides[$name]) && !$this->activeDomainDefault) {
          $this->cache[$cache_key]->setModuleOverride($module_overrides[$name]);
          $list[$name] = $this->cache[$cache_key];
        }
        $this->propagateConfigOverrideCacheability($cache_key, $name);
      }
    }
    return $list;
  }
  /**
   * {@inheritDoc}
   * @see \Drupal\Core\Config\ConfigFactory::doGet()
   */
  protected function doGet($name, $immutable = TRUE) {
    // we never override domain configs so in order not to get in infinite loop
    // we return original configuration
    $name_parts = explode('.', $name);
    if($name_parts[0] == 'domain') {
      return parent::doGet($name, $immutable);
    }

    // Do not apply overrides if configuring 'all' domains.
    if ($this->activeDomainDefault) {
      return parent::doGet($name, $immutable);
    }

    if ($config = $this->doLoadMultiple(array($name), $immutable)) {
      return $config[$name];
    }
    else {
      // If the configuration object does not exist in the configuration
      // storage, create a new object.
      $config = $this->createConfigObject($name, $immutable);
      // Load module overrides so that domain specific config is loaded in admin forms.
      $overrides = $this->loadOverrides(array($name));
      if (isset($overrides[$name])) {
        $config->setModuleOverride($overrides[$name]);
      }
      // Apply any settings.php overrides.
      if ($immutable && isset($GLOBALS['config'][$name])) {
        $config->setSettingsOverride($GLOBALS['config'][$name]);
      }
      foreach ($this->configFactoryOverrides as $override) {
        $config->addCacheableDependency($override->getCacheableMetadata($name));
      }
      return $config;
    }
  }


}
