<?php

namespace Drupal\training_entity_config\Entity;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Annotation\ConfigEntityType;



/**
 *
 * @ConfigEntityType(
 *   id = "announcement",
 *   label= @Translation("Site Announcement"),
 *   handlers= {
 *      "list_builder" = "Drupal\training_entity_config\SiteAnnouncementListBuilder",
 *      "form" = {
 *         "default" = "Drupal\training_entity_config\SiteAnnouncementForm",
 *         "add" = "Drupal\training_entity_config\SiteAnnouncementForm",
 *         "edit" = "Drupal\training_entity_config\SiteAnnouncementForm",
 *         "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *      }
 *   },
 *   config_prefix = "announcement",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/sa/{announcement}",
 *     "delete-form" = "/admin/config/sa/{announcement}/delete",
 *     "edit-form" = "/admin/config/sa/{announcement}/edit",
 *     "collection" = "/admin/config/sa",
 *   },
 *   config_export={
 *     "id",
 *     "label",
 *     "message",
 *   }
 * )
 */
class SiteAnnouncement extends  ConfigEntityBase implements SiteAnnouncementInterface{

  protected $message;

  public function getMessage() {
    return $this->message;
  }
}