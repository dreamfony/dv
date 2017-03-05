<?php

namespace Drupal\dmt_domain_config\Config;

use Drupal\multiversion\Entity\Storage\Sql\BlockStorage as MultiversionBlockStorage;

/**
 * A base entity storage class.
 */
class BlockStorage extends MultiversionBlockStorage {

  use ConfigEntityStorageTrait;

}
