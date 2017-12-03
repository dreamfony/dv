<?php

namespace Drupal\dmt_core\Entity\Storage\Sql;

use Drupal\multiversion\Entity\Storage\ContentEntityStorageTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\profile\ProfileStorage as CoreProfileStorage;
use Drupal\dmt_core\ProfileStorageInterface;

/**
 * Defines the entity storage for profile.
 */
class ProfileStorage extends CoreProfileStorage implements ProfileStorageInterface {

  use ContentEntityStorageTrait;

  /**
   * {@inheritdoc}
   */
  public function loadByUser(AccountInterface $account, $profile_type, $active = TRUE) {
    $this->isDeleted = FALSE;
    $result = $this->loadByProperties([
      'uid' => $account->id(),
      'type' => $profile_type,
      'status' => $active,
    ]);

    return reset($result);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultipleByUser(AccountInterface $account, $profile_type, $active = TRUE) {
    $this->isDeleted = FALSE;
    return $this->loadByProperties([
      'uid' => $account->id(),
      'type' => $profile_type,
      'status' => $active,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function loadDefaultByUser(AccountInterface $account, $profile_type) {
    $this->isDeleted = FALSE;
    $result = $this->loadByProperties([
      'uid' => $account->id(),
      'type' => $profile_type,
      'status' => TRUE,
      'is_default' => TRUE,
    ]);

    return reset($result);
  }

}
