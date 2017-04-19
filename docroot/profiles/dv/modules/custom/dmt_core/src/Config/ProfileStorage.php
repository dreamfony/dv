<?php

namespace Drupal\dmt_core\Config;

use Drupal\profile\ProfileStorage as CoreProfileStorage;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageTrait;

/**
 * A base entity storage class.
 */
class ProfileStorage extends CoreProfileStorage {

  use ContentEntityStorageTrait;

}
