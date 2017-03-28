<?php

namespace Drupal\activity_creator\Plugin;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Component\Plugin\Factory\FactoryInterface;

/**
 * Thin interface for the activity action plugin manager.
 *
 * @ingroup activity_action
 */
interface ActivityActionManagerInterface extends DiscoveryInterface, FactoryInterface {
  /**
   * Retrieves an options list of available trackers.
   *
   * @return string[]
   *   An associative array mapping the IDs of all available tracker plugins to
   *   their labels.
   */
  public function getOptionsList();
}
