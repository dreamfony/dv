<?php
namespace Drupal\dmt_domain_config\Config;

use Drupal\Core\Config\ConfigFactory as CoreConfigFactory;
use Drupal\dmt_domain_config\DomainNegotiator;
/**
 * Overrides Drupal\Core\Config\ConfigFactory in order to use our own Config class.
 */
class ConfigFactory extends CoreConfigFactory {
  /**
   * The Domain negotiator.
   *
   * @var \Drupal\dmt_domain_config\DomainNegotiator
   */
  protected $domainNegotiator;

  /**
   * {@inheritDoc}
   * @see \Drupal\Core\Config\ConfigFactory::createConfigObject()
   */
  protected function createConfigObject($name, $immutable) {
    if (!$immutable) {
      $config = new Config($name, $this->storage, $this->eventDispatcher, $this->typedConfigManager);
      // Pass the negotiator to the Config object.
      $config->setDomainNegotiator($this->domainNegotiator);
      return $config;
    }
    return parent::createConfigObject($name, $immutable);
  }

  /**
   * Set the Domain negotiator.
   * @param DomainNegotiator $domain_negotiator
   */
  public function setDomainNegotiator(DomainNegotiator $domain_negotiator) {
    $this->domainNegotiator = $domain_negotiator;
  }

  /**
   * {@inheritdoc}
   */
  public function getEditable($name) {

    if (!$this->domainNegotiator->isActiveDomainDefault()) {
      $mutable = $this->doGet($name, FALSE);
      $overrides = $this->loadOverrides([$name]);

      if(isset($overrides[$name])) {
        $overrides = $overrides[$name];
        foreach ($overrides as $key => $value) {
          $mutable->set($key, $value);
        }
      }

      return $mutable;
    }


    return $this->doGet($name, FALSE);
  }


}
