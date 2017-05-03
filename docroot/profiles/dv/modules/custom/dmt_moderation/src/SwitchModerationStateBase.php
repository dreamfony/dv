<?php

namespace Drupal\dmt_moderation;

use Drupal\Component\Plugin\PluginBase;
use Drupal\content_moderation\ModerationInformation;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\content_moderation\StateTransitionValidation;
use Drupal\Core\Session\AccountInterface;

/**
 * Base class for Switch Moderation State plugins.
 */
class SwitchModerationStateBase extends PluginBase implements SwitchModerationStateInterface, ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\content_moderation\StateTransitionValidation
   */
  protected $stateTransitionValidation;

  /**
   * @var \Drupal\content_moderation\ModerationInformation
   */
  protected $moderationInformation;

  /**
   * Constructs a ActivityModerationBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\content_moderation\StateTransitionValidation $stateTransitionValidation
   * @param \Drupal\content_moderation\ModerationInformation $moderationInformation
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateTransitionValidation $stateTransitionValidation, ModerationInformation $moderationInformation) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->stateTransitionValidation = $stateTransitionValidation;
    $this->moderationInformation = $moderationInformation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('content_moderation.state_transition_validation'),
      $container->get('content_moderation.moderation_information')
    );
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param string $state_id
   * @param string $old_state
   */
  public function switchState(ContentEntityInterface &$entity, AccountInterface $account, $old_state, $state_id) {
    /** @var \Drupal\workflows\Transition $transition */
    if($transition = $this->getTransition($entity, $account, $old_state, $state_id)) {

      // if method named same as a transition exists call that method
      $switch_method = $transition->id() . '_switch';

      if (is_callable(array($this, $switch_method))) {
        $this->$switch_method($entity, $account);
      }
    }
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param string $state_id
   * @param string $old_state
   */
  public function switchStateValidate(ContentEntityInterface &$entity, AccountInterface $account, $old_state, $state_id) {
    /** @var \Drupal\workflows\Transition $transition */
    if($transition = $this->getTransition($entity, $account, $old_state, $state_id)) {

      // if method named same as a transition exists call that method
      $validate_method = $transition->id() . '_validate';

      if (is_callable(array($this, $validate_method))) {
        return $this->$validate_method($entity, $account);
      }
    }
  }

  /**
   * Is Valid Transition method. This method is not used but its meant to be used for access check.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param string $old_state
   * @param string $state_id
   * @return bool
   */
  public function isValidTransition(ContentEntityInterface $entity, AccountInterface $account, $old_state, $state_id) {
    return $this->getTransition($entity, $account, $old_state, $state_id) ? TRUE : FALSE;
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param string $old_state
   * @param string $state_id
   * @return bool|\Drupal\workflows\Transition
   */
  private function getTransition(ContentEntityInterface $entity, AccountInterface $account, $old_state, $state_id) {
    // get original entity
    $original_entity = $this->moderationInformation->getLatestRevision($entity->getEntityTypeId(), $entity->id());
    if (!$entity->isDefaultTranslation() && $original_entity->hasTranslation($entity->language()->getId())) {
      $original_entity = $original_entity->getTranslation($entity->language()->getId());
    }

    $valid_transitions = $this->stateTransitionValidation->getValidTransitions($original_entity, $account);

    // check if user can transition entity to a given $state_id
    foreach ($valid_transitions as $valid_transition) {
      /** @var \Drupal\workflows\Transition $valid_transition */
      if( $state_id != $old_state && $state_id == $valid_transition->to()->id()) {
        return $valid_transition;
      }
    }

    return FALSE;
  }

}

