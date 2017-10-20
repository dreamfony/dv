<?php

namespace Drupal\training_entity_config\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

interface SiteAnnouncementInterface extends ConfigEntityInterface {

  /**
   * @return string
   */
  public  function  getMessage();
}