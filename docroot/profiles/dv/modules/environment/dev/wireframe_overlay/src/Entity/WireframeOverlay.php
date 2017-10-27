<?php

namespace Drupal\wireframe_overlay\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Wireframe overlay entity.
 *
 * @ConfigEntityType(
 *   id = "wireframe_overlay",
 *   label = @Translation("Wireframe overlay"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\wireframe_overlay\WireframeOverlayListBuilder",
 *     "form" = {
 *       "add" = "Drupal\wireframe_overlay\Form\WireframeOverlayForm",
 *       "edit" = "Drupal\wireframe_overlay\Form\WireframeOverlayForm",
 *       "delete" = "Drupal\wireframe_overlay\Form\WireframeOverlayDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\wireframe_overlay\WireframeOverlayHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "wireframe_overlay",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/wireframe_overlay/{wireframe_overlay}",
 *     "add-form" = "/admin/structure/wireframe_overlay/add",
 *     "edit-form" = "/admin/structure/wireframe_overlay/{wireframe_overlay}/edit",
 *     "delete-form" = "/admin/structure/wireframe_overlay/{wireframe_overlay}/delete",
 *     "collection" = "/admin/structure/wireframe_overlay"
 *   }
 * )
 */
class WireframeOverlay extends ConfigEntityBase implements WireframeOverlayInterface {

  /**
   * The Wireframe overlay ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Wireframe overlay label.
   *
   * @var string
   */
  protected $label;

}
