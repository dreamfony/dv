<?php

namespace Drupal\activity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Activity type entity.
 *
 * @ConfigEntityType(
 *   id = "activity_entity_type",
 *   label = @Translation("Activity type"),
 *   handlers = {
 *     "list_builder" = "Drupal\activity\ActivityEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\activity\Form\ActivityEntityTypeForm",
 *       "edit" = "Drupal\activity\Form\ActivityEntityTypeForm",
 *       "delete" = "Drupal\activity\Form\ActivityEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\activity\ActivityEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "activity_entity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "activity_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/activity_entity_type/{activity_entity_type}",
 *     "add-form" = "/admin/structure/activity_entity_type/add",
 *     "edit-form" = "/admin/structure/activity_entity_type/{activity_entity_type}/edit",
 *     "delete-form" = "/admin/structure/activity_entity_type/{activity_entity_type}/delete",
 *     "collection" = "/admin/structure/activity_entity_type"
 *   }
 * )
 */
class ActivityEntityType extends ConfigEntityBundleBase implements ActivityEntityTypeInterface {

  /**
   * The Activity type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Activity type label.
   *
   * @var string
   */
  protected $label;

}
