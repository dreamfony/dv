<?php

namespace Drupal\activity_creator;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class ActivityStorage extends SqlContentEntityStorage implements ActivityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ActivityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {activity_revision} WHERE id=:id ORDER BY vid',
      array(':id' => $entity->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {activity_field_revision} WHERE uid = :uid ORDER BY vid',
      array(':uid' => $account->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(ActivityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {activity_field_revision} WHERE id = :id AND default_langcode = 1', array(':id' => $entity->id()))
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('activity_revision')
      ->fields(array('langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED))
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
