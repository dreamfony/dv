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
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * SwitchModerationStateBase constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\content_moderation\StateTransitionValidation $stateTransitionValidation
   * @param \Drupal\content_moderation\ModerationInformation $moderationInformation
   * @param \Drupal\Core\Session\AccountInterface $account
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateTransitionValidation $stateTransitionValidation, ModerationInformation $moderationInformation, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->stateTransitionValidation = $stateTransitionValidation;
    $this->moderationInformation = $moderationInformation;
    $this->account = $account;
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
      $container->get('content_moderation.moderation_information'),
      $container->get('current_user')
    );
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function switchState(ContentEntityInterface $entity) {
    /** @var \Drupal\workflows\Transition $transition */
    if ($transition = $this->getTransition($entity)) {

      // if method named same as a transition exists call that method
      $switch_method = $transition->id() . '_switch';

      if (is_callable(array($this, $switch_method))) {
        $this->$switch_method($entity);
      }
    }
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function switchStateValidate(ContentEntityInterface $entity) {
    /** @var \Drupal\workflows\Transition $transition */
    if ($transition = $this->getTransition($entity)) {

      // if method named same as a transition exists call that method
      $validate_method = $transition->id() . '_validate';

      if (is_callable(array($this, $validate_method))) {
        return $this->$validate_method($entity);
      }
    }
  }

  /**
   * Is Valid Transition method. This method is not used but its meant to be used for access check.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return bool
   */
  public function isValidTransition(ContentEntityInterface $entity) {
    return $this->getTransition($entity) ? TRUE : FALSE;
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return array|\Drupal\workflows\Transition
   */
  private function getTransition(ContentEntityInterface $entity) {
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
            return $valid_transition;
          }
        }

      }
    }
  }

}
