<?php

namespace Drupal\moderation_state_machine;

use Drupal\content_moderation\ContentModerationStateInterface;
use Drupal\content_moderation\ModerationInformation;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\content_moderation\StateTransitionValidation;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\workflows\Transition;
use Drupal\Core\Link;
use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for Activity moderation plugins.
 */
class ModerationStateLinks {

  use StringTranslationTrait;

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

  protected $routeName = 'moderation_state_machine.switch_moderation_state';

  protected $routeParameters;

  /**
   * ModerationStateLinks constructor.
   *
   * @param \Drupal\content_moderation\StateTransitionValidation $stateTransitionValidation
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param \Drupal\content_moderation\ModerationInformation $moderationInformation
   */
  public function __construct(StateTransitionValidation $stateTransitionValidation, AccountInterface $account, ModerationInformation $moderationInformation) {
    $this->stateTransitionValidation = $stateTransitionValidation;
    $this->account = $account;
    $this->moderationInformation = $moderationInformation;
  }

  public function getLinksAccess(ContentEntityInterface $entity) {
    if(!$this->moderationInformation->isModeratedEntity($entity)) {
      return FALSE;
    }

    $valid_transitions = $this->stateTransitionValidation->getValidTransitions($entity, $this->account);

    foreach ($valid_transitions as $transition) {
      /** @var \Drupal\workflows\Transition $transition */
      if($link = $this->getUrl($entity, $transition)) {
        return TRUE;
      }
    }

    return FALSE;
  }


  /**
   * Get Links.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return array
   */
  public function getLinks(ContentEntityInterface $entity) {
    // get original entity
    $original_entity = $this->moderationInformation->getLatestRevision($entity->getEntityTypeId(), $entity->id());
    if (!$entity->isDefaultTranslation() && $original_entity->hasTranslation($entity->language()
        ->getId())
    ) {
      $original_entity = $original_entity->getTranslation($entity->language()
        ->getId());
    }

    $valid_transitions = $this->stateTransitionValidation->getValidTransitions($original_entity, $this->account);

    $links = [
      'moderation' => array(
        'classes' => 'dropdown',
        'link_attributes' => 'data-toggle=dropdown aria-expanded=true aria-haspopup=true role=button',
        'link_classes' => 'dropdown-toggle clearfix',
        'icon_classes' => 'icon-add_box',
        'title' => $this->t('Create New Content'),
        'label' => $this->t('New content'),
        'title_classes' => 'sr-only',
        'url' => '#',
        'below' => array(),
      )
    ];

    foreach ($valid_transitions as $transition) {
      /** @var \Drupal\workflows\Transition $transition */
      if($original_entity->moderation_state->value != $transition->to()->id() && $url = $this->getUrl($entity, $transition)) {
        $links['moderation']['below'][$transition->id()] = [
          'classes' => '',
          'link_attributes' => '',
          'link_classes' => '',
          'icon_classes' => '',
          'icon_label' => '',
          'title' => $transition->label(),
          'label' => $transition->label(),
          'title_classes' => '',
          'url' => $url,
        ];
      }
    }

    return [
      '#theme' => 'moderation_state_links',
      '#links' => $links,
    ];
  }

  /**
   * Get Moderation State Url.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param \Drupal\workflows\Transition $transition
   * @return bool|\Drupal\Core\Url
   */
  private function getUrl(ContentEntityInterface $entity, Transition $transition) {
    $this->routeParameters = [
      'entity_type' => $entity->getEntityTypeId(),
      'entity' => $entity->id(),
      'state_id' => $transition->to()->id()
    ];

    $url = Url::fromRoute($this->routeName, $this->routeParameters);

    if ($this->urlAccess($entity, $transition->to()->id())) {
      return $url;
    }

    return FALSE;
  }

  /**
   * Checks access for switch moderation state url.
   *
   * @param ContentEntityInterface $entity
   *   Content Entity
   * @param string $state_id
   *   State Id
   *
   * @return bool
   */
  private function urlAccess(ContentEntityInterface $entity, $state_id) {
    $entity->set('moderation_state', $state_id);

    $causes = [];

    $violations = $entity->moderation_state->validate();
    if ($violations->count()) {
      foreach ($violations as $violation) {
        /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
        if($violation->getCause() == 'allow_link' ) {
          $causes[] = $violation->getCause();
        } else {
          $causes[] = 'forbidden';
        }
      }
    }

    if(!in_array('forbidden', $causes)) {
      return TRUE;
    }

    return FALSE;
  }

}

