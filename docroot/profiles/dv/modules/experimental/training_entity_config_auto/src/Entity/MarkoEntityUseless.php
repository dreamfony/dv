<?php

namespace Drupal\training_entity_config_auto\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Marko entity useless entity.
 *
 * @ConfigEntityType(
 *   id = "marko_entity_useless",
 *   label = @Translation("Marko entity useless"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\training_entity_config_auto\MarkoEntityUselessListBuilder",
 *     "form" = {
 *       "add" = "Drupal\training_entity_config_auto\Form\MarkoEntityUselessForm",
 *       "edit" = "Drupal\training_entity_config_auto\Form\MarkoEntityUselessForm",
 *       "delete" = "Drupal\training_entity_config_auto\Form\MarkoEntityUselessDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\training_entity_config_auto\MarkoEntityUselessHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "marko_entity_useless",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/useless_marko/marko_entity_useless/{marko_entity_useless}",
 *     "add-form" = "/admin/structure/useless_marko/marko_entity_useless/add",
 *     "edit-form" = "/admin/structure/useless_marko/marko_entity_useless/{marko_entity_useless}/edit",
 *     "delete-form" = "/admin/structure/useless_marko/marko_entity_useless/{marko_entity_useless}/delete",
 *     "collection" = "/admin/structure/useless_marko/marko_entity_useless"
 *   }
 * )
 */
class MarkoEntityUseless extends ConfigEntityBase implements MarkoEntityUselessInterface {

  /**
   * The Marko entity useless ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Marko entity useless label.
   *
   * @var string
   */
  protected $label;


  protected $message;

  public function getMessage() {
    return $this->message;
  }
}
