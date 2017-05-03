<?php

namespace Drupal\dmt_moderation;

use Drupal\content_moderation\ContentModerationStateInterface;
use Drupal\content_moderation\ModerationInformation;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\content_moderation\StateTransitionValidation;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\workflows\Transition;
use Drupal\Core\Link;
use Drupal\content_moderation\ModerationInformationInterface;

/**
 * Base class for Activity moderation plugins.
 */
class ModerationStateLinks {

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


  /**
   * Get Links.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return array|bool
   */
  public function getLinks(ContentEntityInterface $entity) {
    if(!$this->moderationInformation->isModeratedEntity($entity)) {
      return FALSE;
    }

    /// @todo: this method can be cached per request just need to figure out weather its necessary
    // since we are caching the block
    $links = [];

    $valid_transitions = $this->stateTransitionValidation->getValidTransitions($entity, $this->account);
    foreach ($valid_transitions as $transition) {
      /** @var \Drupal\workflows\Transition $transition */
      $links[] = $this->getLink($entity, $transition);
    }

    if (!empty($links)) {
      return $links;
    }

    return FALSE;
  }

  /**
   * Get Link.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param \Drupal\workflows\Transition $transition
   * @return array|\mixed[]|void
   */
  private function getLink(ContentEntityInterface $entity, Transition $transition) {
    $url = Url::fromRoute('dmt_moderation.switch_moderation_state', [
      'entity_type' => $entity->getEntityTypeId(),
      'entity' => $entity->id(),
      'state_id' => $transition->to()->id()
    ]);

    if ($url->access()) {
      $link = Link::fromTextAndUrl($transition->label(), $url);
      /// @todo this method should probably just return urls
      return $link->toRenderable();
    }
    return;
  }


}

