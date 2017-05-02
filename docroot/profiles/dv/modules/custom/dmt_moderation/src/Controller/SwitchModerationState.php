<?php

namespace Drupal\dmt_moderation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\dmt_moderation\Plugin\Type\SwitchModerationStateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class SwitchModerationState
 * @package Drupal\dmt_moderation\Controller
 */
class SwitchModerationState extends ControllerBase {

  /**
   * @var \Drupal\dmt_moderation\Plugin\Type\SwitchModerationStateManager
   */
  protected $switchModerationStateManager;

  /**
   * SwitchModerationState constructor.
   * @param \Drupal\dmt_moderation\Plugin\Type\SwitchModerationStateManager $switchModerationStateManager
   */
  public function __construct(SwitchModerationStateManager $switchModerationStateManager) {
    $this->switchModerationStateManager = $switchModerationStateManager;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.switch_moderation_state_manager')
    );
  }

  /**
   * Switch method.
   *
   * @param $entity_type
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param $state_id
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function switch($entity_type, ContentEntityInterface $entity, $state_id) {
    $entity->set('moderation_state', $state_id);

    /** @var \Drupal\Core\Entity\EntityConstraintViolationList $violations */
    // Validate the entity before saving.
    $violations = $entity->validate();
    if ($violations->count()) {
      foreach ($violations as $violation) {
        drupal_set_message($this->t('@message', [
          '@message' => $violation->getMessage()
        ]), 'error');
      }
      return new RedirectResponse($entity->toUrl()->toString(), 302);
    }

    $entity->save();

    return new RedirectResponse($entity->toUrl()->toString(), 302);
  }

}
