<?php

/**
 * @file
 * Contains \Drupal\activity_creator\Plugin\ActivityEntityConditionInterface.
 */

namespace Drupal\activity_creator\Plugin\Type;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines an interface for Activity entity condition plugins.
 */
interface ActivityEntityConditionInterface extends PluginInspectionInterface {

  /**
   * Checks if this is a valid entity condition for the action.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return mixed
   */
  public function isValidEntityCondition(ContentEntityInterface $entity);

}
