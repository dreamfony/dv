<?php

namespace Drupal\dmt_moderation\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\content_moderation\StateTransitionValidation;
use Drupal\dmt_moderation\Plugin\Type\SwitchModerationStateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the Entity Moderation constraint.
 */
class EntityModerationConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The state transition validation.
   *
   * @var \Drupal\content_moderation\StateTransitionValidation
   */
  protected $validation;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The moderation info.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInformation;

  /**
   * @var \Drupal\dmt_moderation\Plugin\Type\SwitchModerationStateManager
   */
  protected $switchModerationStateManager;

  /**
   * Creates a new ModerationStateConstraintValidator instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\content_moderation\StateTransitionValidation $validation
   *   The state transition validation.
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderation_information
   *   The moderation information.
   * @param \Drupal\dmt_moderation\Plugin\Type\SwitchModerationStateManager $switchModerationStateManager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, StateTransitionValidation $validation, ModerationInformationInterface $moderation_information, SwitchModerationStateManager $switchModerationStateManager) {
    $this->validation = $validation;
    $this->entityTypeManager = $entity_type_manager;
    $this->moderationInformation = $moderation_information;
    $this->switchModerationStateManager = $switchModerationStateManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('content_moderation.state_transition_validation'),
      $container->get('content_moderation.moderation_information'),
      $container->get('plugin.manager.switch_moderation_state_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $value->getEntity();

    $original_entity = $this->moderationInformation->getLatestRevision($entity->getEntityTypeId(), $entity->id());
    if (!$entity->isDefaultTranslation() && $original_entity->hasTranslation($entity->language()->getId())) {
      $original_entity = $original_entity->getTranslation($entity->language()->getId());
    }

    $workflow = $this->moderationInformation->getWorkflowForEntity($entity);
    $new_state = $workflow->getState($entity->moderation_state->value) ?: $workflow->getInitialState();
    $original_state = $workflow->getState($original_entity->moderation_state->value);

    $plugin_id = $this->switchModerationStateManager->getPluginIdByEntity($entity);

    /** @var \Drupal\dmt_moderation\SwitchModerationStateBase $sms */
    $sms = $this->switchModerationStateManager->createInstance($plugin_id);
    $account = \Drupal::currentUser();
    $violations = $sms->switchStateValidate($entity, $account, $original_state->id(), $new_state->id());

    if(!empty($violations)) {
      foreach ($violations as $violation) {
        $this->context->addViolation($violation);
      }
    }
  }
}
