<?php

namespace Drupal\training_entity_config;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\training_entity_config\Entity\SiteAnnouncementInterface;

class SiteAnnouncementListBuilder extends ConfigEntityListBuilder{

  public function buildHeader() {
    $header["label"] = t("header");
    return $header + parent::buildHeader();
  }

  public function buildRow(SiteAnnouncementInterface $entity) {
    $row['label'] = $entity->label();
    return $row + parent::buildRow($entity);
  }
}