<?php

namespace Drupal\activity_creator\Plugin\GroupContentEnabler;

use Drupal\Component\Plugin\Derivative\DeriverBase;

class ActivityDeriver extends DeriverBase {

  /**
   * {@inheritdoc}.
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
      $this->derivatives['activity'] = [
        'entity_bundle' => 'activity',
        'label' => t('Activity'),
        'description' => t('Adds activity to groups.'),
      ] + $base_plugin_definition;

    return $this->derivatives;
  }

}
