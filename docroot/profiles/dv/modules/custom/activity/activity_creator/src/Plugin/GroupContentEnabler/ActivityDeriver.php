<?php

namespace Drupal\activity_creator\Plugin\GroupContentEnabler;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\activity_creator\Entity\ActivityType;

class ActivityDeriver extends DeriverBase {

  /**
   * {@inheritdoc}.
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach (ActivityType::loadMultiple() as $name => $activity_type) {
      /** @var ActivityType $activity_type */
      $label = $activity_type->label();

      $this->derivatives[$name] = [
          'entity_bundle' => $name,
          'label' => t('Group activity') . " ($label)",
          'description' => t('Adds %type content to groups both publicly and privately.', ['%type' => $label]),
        ] + $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
