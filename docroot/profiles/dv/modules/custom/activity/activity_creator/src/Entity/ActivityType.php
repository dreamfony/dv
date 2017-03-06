<?php

namespace Drupal\activity_creator\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Activity type entity.
 *
 * @ConfigEntityType(
 *   id = "activity_type",
 *   label = @Translation("Activity type"),
 *   handlers = {
 *     "list_builder" = "Drupal\activity_creator\ActivityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\activity_creator\Form\ActivityTypeForm",
 *       "edit" = "Drupal\activity_creator\Form\ActivityTypeForm",
 *       "delete" = "Drupal\activity_creator\Form\ActivityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\activity_creator\ActivityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "activity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "activity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/activity_type/{activity_type}",
 *     "add-form" = "/admin/structure/activity_type/add",
 *     "edit-form" = "/admin/structure/activity_type/{activity_type}/edit",
 *     "delete-form" = "/admin/structure/activity_type/{activity_type}/delete",
 *     "collection" = "/admin/structure/activity_type"
 *   }
 * )
 */
class ActivityType extends ConfigEntityBundleBase implements ActivityTypeInterface {

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
