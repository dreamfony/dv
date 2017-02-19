<?php

namespace Drupal\activity\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Activity entities.
 *
 * @ingroup activity
 */
interface ActivityEntityInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Activity type.
   *
   * @return string
   *   The Activity type.
   */
  public function getType();

  /**
   * Gets the Activity name.
   *
   * @return string
   *   Name of the Activity.
   */
  public function getName();

  /**
   * Sets the Activity name.
   *
   * @param string $name
   *   The Activity name.
   *
   * @return \Drupal\activity\Entity\ActivityEntityInterface
   *   The called Activity entity.
   */
  public function setName($name);

  /**
   * Gets the Activity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Activity.
   */
  public function getCreatedTime();

  /**
   * Sets the Activity creation timestamp.
   *
   * @param int $timestamp
   *   The Activity creation timestamp.
   *
   * @return \Drupal\activity\Entity\ActivityEntityInterface
   *   The called Activity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Activity published status indicator.
   *
   * Unpublished Activity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Activity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Activity.
   *
   * @param bool $published
   *   TRUE to set this Activity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\activity\Entity\ActivityEntityInterface
   *   The called Activity entity.
   */
  public function setPublished($published);

  /**
   * Gets the Activity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Activity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\activity\Entity\ActivityEntityInterface
   *   The called Activity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Activity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Activity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\activity\Entity\ActivityEntityInterface
   *   The called Activity entity.
   */
  public function setRevisionUserId($uid);

}
