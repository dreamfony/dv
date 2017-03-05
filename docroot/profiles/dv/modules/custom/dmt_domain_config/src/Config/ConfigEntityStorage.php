<?php

namespace Drupal\dmt_domain_config\Config;

use Drupal\Core\Config\Entity\ConfigEntityStorage as CoreConfigEntityStorage;

/**
 * A base entity storage class.
 */
class ConfigEntityStorage extends CoreConfigEntityStorage {

  use ConfigEntityStorageTrait;

}
