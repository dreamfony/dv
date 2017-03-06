<?php

namespace Drupal\activity_creator;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\activity_creator\Entity\ActivityInterface;

/**
 * Defines the storage handler class for Activity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Activity entities.
 *
 * @ingroup activity_creator
 */
interface ActivityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Activity revision IDs for a specific Activity.
   *
   * @param \Drupal\activity_creator\Entity\ActivityInterface $entity
   *   The Activity entity.
   *
   * @return int[]
   *   Activity revision IDs (in ascending order).
   */
  public function revisionIds(ActivityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Activity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Activity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\activity_creator\Entity\ActivityInterface $entity
   *   The Activity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ActivityInterface $entity);

  /**
   * Unsets the language for all Activity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
