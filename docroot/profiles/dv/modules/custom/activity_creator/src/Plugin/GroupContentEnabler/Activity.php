<?php

namespace Drupal\activity_creator\Plugin\GroupContentEnabler;

use Drupal\group\Entity\GroupInterface;
use Drupal\group\Plugin\GroupContentEnablerBase;
use Drupal\group\Entity\GroupType;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a content enabler for activity.
 *
 * @GroupContentEnabler(
 *   id = "activity",
 *   label = @Translation("Activity"),
 *   description = @Translation("Adds activity to groups."),
 *   entity_type_id = "activity",
 *   pretty_path_key = "activity",
 *   deriver = "Drupal\activity_creator\Plugin\GroupContentEnabler\ActivityDeriver"
 * )
 */
class Activity extends GroupContentEnablerBase {

  /**
   * Retrieves the group type this plugin supports.
   *
   * @return \Drupal\group\Entity\GroupTypeInterface
   *   The group type this plugin supports.
   */
  protected function getSubgroupType() {
    return GroupType::load($this->getEntityBundle());
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupOperations(GroupInterface $group) {
    $operations = [];
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config['entity_cardinality'] = 1;

    // This string will be saved as part of the group type config entity. We do
    // not use a t() function here as it needs to be stored untranslated.
    $config['info_text']['value'] = '<p>By submitting this form you will add activity to the group.<br />It will then be subject to the access control settings that were configured for the group.<br/>Please fill out any available fields to describe the relation between the subgroup and the group.</p>';
    return $config;
  }

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
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return ['config' => ['group.type.' . $this->getEntityBundle()]];
  }

}
