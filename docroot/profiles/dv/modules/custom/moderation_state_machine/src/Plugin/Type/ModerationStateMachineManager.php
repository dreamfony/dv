<?php

/**
 * @file
 * Contains \Drupal\activity_creator\Plugin\ActivityActionManager.
 */

namespace Drupal\moderation_state_machine\Plugin\Type;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Utility\Html;
use Drupal\content_moderation\StateTransitionValidation;
use Drupal\Core\Session\AccountInterface;
use Drupal\content_moderation\ModerationInformation;

/**
 * Provides the Activity Moderation plugin manager.
 */
class ModerationStateMachineManager extends DefaultPluginManager {

  /**
   * @var \Drupal\content_moderation\StateTransitionValidation
   */
  protected $stateTransitionValidation;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * @var \Drupal\content_moderation\ModerationInformation
   */
  protected $moderationInformation;

  /**
   * Constructor for Moderation State Machine objects.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   *
   * @param \Drupal\content_moderation\StateTransitionValidation $stateTransitionValidation
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param \Drupal\content_moderation\ModerationInformation $moderationInformation
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, StateTransitionValidation $stateTransitionValidation, AccountInterface $account, ModerationInformation $moderationInformation) {
    parent::__construct('Plugin/ModerationStateMachine', $namespaces, $module_handler, 'Drupal\moderation_state_machine\ModerationStateMachineInterface', 'Drupal\moderation_state_machine\Annotation\ModerationStateMachine');

    $this->alterInfo('moderation_state_machine_info');
    $this->setCacheBackend($cache_backend, 'moderation_state_machine_plugins');

    $this->stateTransitionValidation = $stateTransitionValidation;
    $this->account = $account;
    $this->moderationInformation = $moderationInformation;
  }

  /**
   * Retrieves an options list of available trackers.
   *
   * @return string[]
   *   An associative array mapping the IDs of all available tracker plugins to
   *   their labels.
   */
  public function getOptionsList() {
    $options = array();
    foreach ($this->getDefinitions() as $plugin_id => $plugin_definition) {
      $options[$plugin_id] = Html::escape($plugin_definition['label']);
    }
    return $options;
  }

  /**
   * Get Plugin Id By Entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return array
   */
  public function getPluginId(ContentEntityInterface $entity) {
    $plugin_ids = [];

    $transition_id = $this->getTransitionId($entity);

    foreach ($this->getDefinitions() as $plugin_id => $plugin_definition) {
      // skip default plugin
      if($plugin_id == 'moderation_state_machine_default') {
        continue;
      }

      // skip disabled plugins
      if(isset($plugin_definition['status']) && $plugin_definition['status'] == 0) {
        continue;
      }

      if($plugin_definition['transition_id'] == $transition_id && $plugin_definition['entity_type'] == $entity->getEntityTypeId() && $plugin_definition['entity_bundle'] == $entity->bundle()) {
        // if weight is not set we set it to 0
        $plugin_definition['weight'] = isset($plugin_definition['weight']) ? $plugin_definition['weight'] : 0;
        $plugin_ids[$plugin_definition['weight']] = $plugin_id;
      }
    }

    if(!empty($plugin_ids)) {
      // sort plugin_ids by weight
      asort($plugin_ids, SORT_NUMERIC);
      return $plugin_ids;
    }

    // if no other plugins are implemented return default
    return ['moderation_state_machine_default'];
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return integer
   */
  private function getTransitionId(ContentEntityInterface $entity) {
    // get original entity
    $original_entity = $this->moderationInformation->getLatestRevision($entity->getEntityTypeId(), $entity->id());
    if (!$entity->isDefaultTranslation() && $original_entity->hasTranslation($entity->language()
        ->getId())
    ) {
      $original_entity = $original_entity->getTranslation($entity->language()
        ->getId());
    }

    // get valid transitions for original entity
    $valid_transitions = $this->stateTransitionValidation->getValidTransitions($original_entity, $this->account);

    // check if user can transition entity
    foreach ($valid_transitions as $valid_transition) {
      /** @var \Drupal\workflows\Transition $valid_transition */
      if ($entity->moderation_state->value != $original_entity->moderation_state->value && $entity->moderation_state->value == $valid_transition->to()
          ->id()
      ) {
        foreach ($valid_transition->from() as $from_state) {
          /** @var \Drupal\workflows\State $from_state */
          if ($from_state->id() == $original_entity->moderation_state->value) {
            return $valid_transition->id();
          }
        }

      }
    }
  }

}
