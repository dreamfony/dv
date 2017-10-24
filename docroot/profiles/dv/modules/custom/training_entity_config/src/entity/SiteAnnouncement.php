<?php

namespace Drupal\training_entity_config\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;


/**
 *
 * @ConfigEntityType(
 *   id = "announcement",
 *   label= @Translation("Site Announcement"),
 *   handlers= {
 *      "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *      "list_builder" = "Drupal\training_entity_config\SiteAnnouncementListBuilder",
 *      "form" = {
 *         "add" = "Drupal\training_entity_config\SiteAnnouncementForm",
 *         "edit" = "Drupal\training_entity_config\SiteAnnouncementForm",
 *         "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *      },
 *       "route_provider" = {
 *       "html" = "Drupal\training_entity_config\SiteAnnouncementRouteProvider",
 *     },
 *   },
 *   config_prefix = "announcement",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/sa/{announcement}",
 *     "add-form" = "/admin/config/sa/add",
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

  public $message;

  public function getMessage() {
    return $this->message;
  }
}