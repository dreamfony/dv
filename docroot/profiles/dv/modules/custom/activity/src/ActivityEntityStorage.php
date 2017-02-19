<?php

namespace Drupal\activity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\activity\Entity\ActivityEntityInterface;

/**
 * Defines the storage handler class for Activity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Activity entities.
 *
 * @ingroup activity
 */
class ActivityEntityStorage extends SqlContentEntityStorage implements ActivityEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ActivityEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {activity_entity_revision} WHERE id=:id ORDER BY vid',
      array(':id' => $entity->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {activity_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      array(':uid' => $account->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(ActivityEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {activity_entity_field_revision} WHERE id = :id AND default_langcode = 1', array(':id' => $entity->id()))
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('activity_entity_revision')
      ->fields(array('langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED))
      ->condition('langcode', $language->getId())
      ->execute();
  }

}