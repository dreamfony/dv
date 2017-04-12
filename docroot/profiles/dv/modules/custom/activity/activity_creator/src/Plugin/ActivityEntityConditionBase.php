<?php

/**
 * @file
 * Contains \Drupal\activity_creator\Plugin\ActivityEntityConditionBase.
 */

namespace Drupal\activity_creator\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Base class for Activity entity condition plugins.
 */
abstract class ActivityEntityConditionBase extends PluginBase implements ActivityEntityConditionInterface {

  public function isValidEntityCondition(ContentEntityInterface $entity) {
    return TRUE;
  }

}

