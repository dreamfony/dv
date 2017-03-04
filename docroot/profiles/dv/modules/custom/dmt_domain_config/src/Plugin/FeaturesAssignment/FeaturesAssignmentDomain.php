<?php

namespace Drupal\dmt_domain_config\Plugin\FeaturesAssignment;

use Drupal\features\FeaturesAssignmentMethodBase;
use Drupal\domain\Entity\Domain;

/**
 * Class for assigning configuration to packages based on namespaces.
 *
 * @Plugin(
 *   id = "domain",
 *   weight = 0,
 *   name = @Translation("Domain"),
 *   description = @Translation("Add config to packages that belong to that domain."),
 * )
 */
class FeaturesAssignmentDomain extends FeaturesAssignmentMethodBase {
  /**
   * {@inheritdoc}
   */
  public function assignPackages($force = FALSE) {

    $current_bundle = $this->assigner->getBundle();
    $config_collection = $this->featuresManager->getConfigCollection();

    foreach ($config_collection as $item_name => $item) {
      $name_parts = explode('.', $item_name);
      if ($name_parts[0] == 'domain' && ($name_parts[1] == 'record' || $name_parts[1] == 'config')) {
        /** @var Domain $domain */
        $domain = \Drupal::service('domain.loader')->load($name_parts[2]);
        if(!$domain->isDefault()) {
          if (is_null($this->featuresManager->findPackage('domain_' . $name_parts[2])) && !$item->getPackage()) {
            $description = $this->t('Provides domain @label related configuration.', array('@label' => $domain->get('name')));
            if (isset($item->getData()['description'])) {
              $description .= ' ' . $item->getData()['description'];
            }
            $this->featuresManager->initPackage('domain_' . $name_parts[2], 'Domain ' . $domain->get('name'), $description, 'module', $current_bundle);
          }
          // Update list with the package we just added.
          try {
            $this->featuresManager->assignConfigPackage('domain_' . $name_parts[2], [$item_name]);
          } catch (\Exception $exception) {
            \Drupal::logger('features')->error($exception->getMessage());
          }
        }

      }
    }

  }

}
