<?php

namespace Drupal\dv_positions\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Positions entities.
 *
 * @ingroup dv_positions
 */
interface PositionsInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Positions creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Positions.
   */
  public function getCreatedTime();

  /**
   * Sets the Positions creation timestamp.
   *
   * @param int $timestamp
   *   The Positions creation timestamp.
   *
   * @return \Drupal\dv_positions\Entity\PositionsInterface
   *   The called Positions entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Positions published status indicator.
   *
   * Unpublished Positions are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Positions is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Positions.
   *
   * @param bool $published
   *   TRUE to set this Positions to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\dv_positions\Entity\PositionsInterface
   *   The called Positions entity.
   */
  public function setPublished($published);

}
