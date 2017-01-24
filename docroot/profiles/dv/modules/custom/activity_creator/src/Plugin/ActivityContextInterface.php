<?php

/**
 * @file
 * Contains \Drupal\activity_creator\Plugin\ActivityContextInterface.
 */

namespace Drupal\activity_creator\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines an interface for Activity content plugins.
 */
interface ActivityContextInterface extends PluginInspectionInterface {

  /**
   * Returns a batched list of recipients for this context.
   *
   * Format
   * array(
   *   array (
   *     id = uid or gip
   *     type = "user / group"
   *   )
   * )
   */
  public function getRecipients(array $data, $last_id, $limit);


  /**
   * Determines if the entity is valid for this context.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return mixed
   */
  public function isValidEntity(ContentEntityInterface $entity);
}
