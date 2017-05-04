<?php

namespace Drupal\dmt_moderation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ModerationStateMachine
 * @package Drupal\dmt_moderation\Controller
 */
class ModerationStateMachine extends ControllerBase {

  /**
   * Switch method.
   *
   * @param $entity_type
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param $state_id
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function switchState($entity_type, ContentEntityInterface $entity, $state_id) {
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
