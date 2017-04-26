<?php

namespace Drupal\activity_creator\Plugin\GroupContentEnabler;

use Drupal\group\Plugin\GroupContentEnablerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\activity_creator\Entity\ActivityType;
use Drupal\Core\Url;

/**
 * Provides a content enabler for activity.
 *
 * @GroupContentEnabler(
 *   id = "activity",
 *   label = @Translation("Activity"),
 *   description = @Translation("Adds activity to groups."),
 *   entity_type_id = "activity",
 *   pretty_path_key = "activity",
 *   reference_label = @Translation("Id"),
 *   reference_description = @Translation("The id of the activity to add to the group"),
 *   deriver = "Drupal\activity_creator\Plugin\GroupContentEnabler\ActivityDeriver"
 * )
 */
class Activity extends GroupContentEnablerBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Disable the entity cardinality field as the functionality of this module
    // relies on a cardinality of 1. We don't just hide it, though, to keep a UI
    // that's consistent with other content enabler plugins.
    $info = $this->t("This field has been disabled by the plugin to guarantee the functionality that's expected of it.");
    $form['entity_cardinality']['#disabled'] = TRUE;
    $form['entity_cardinality']['#description'] .= '<br /><em>' . $info . '</em>';

    return $form;
  }

  /**
   * Retrieves the node type this plugin supports.
   *
   * @return \Drupal\activity_creator\Entity\ActivityTypeInterface
   *   The node type this plugin supports.
   */
  protected function getActivityType() {
    return ActivityType::load($this->getEntityBundle());
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupOperations(GroupInterface $group) {
    $account = \Drupal::currentUser();
    $plugin_id = $this->getPluginId();
    $type = $this->getEntityBundle();
    $operations = [];

    if ($group->hasPermission("create $plugin_id entity", $account)) {
      $route_params = ['group' => $group->id(), 'plugin_id' => $plugin_id];
      $operations["activity-create-$type"] = [
        'title' => $this->t('Create @type', ['@type' => $this->getActivityType()->label()]),
        'url' => new Url('entity.activity.create_form', $route_params),
        'weight' => 30,
      ];
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTargetEntityPermissions() {
    $permissions = parent::getTargetEntityPermissions();
    $plugin_id = $this->getPluginId();

    // Add a 'view unpublished' permission by re-using most of the 'view' one.
    $original = $permissions["view $plugin_id entity"];
    $permissions["view unpublished $plugin_id entity"] = [
        'title' => str_replace('View ', 'View unpublished ', $original['title']),
      ] + $original;

    return $permissions;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config['entity_cardinality'] = 1;
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    $dependencies['config'][] = 'activity.type.' . $this->getEntityBundle();
    return $dependencies;
  }

}
