<?php

namespace Drupal\moderation_state_machine\Plugin\ModerationStateMachine;

use Drupal\content_moderation\ModerationInformation;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\moderation_state_machine\ModerationStateMachineBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\content_moderation\StateTransitionValidation;

/**
 * Example plugin for "Editorial workflow".
 * Possible transitions are create_new_draft, publish, archive, archived_draft and archived_published
 *
 * @ModerationStateMachine(
 *  id = "page_state_machine",
 *  entity_type = "node",
 *  entity_bundle = "page",
 *  label = @Translation("Switch Moderation State"),
 * )
 */
class PageStateMachine extends ModerationStateMachineBase implements ContainerFactoryPluginInterface {


  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateTransitionValidation $stateTransitionValidation, ModerationInformation $moderationInformation, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $stateTransitionValidation, $moderationInformation, $account);
  }


  /**
   * Validates publish transition
   * TransitionId_validate
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return array
   */
  public function publish_validate(ContentEntityInterface $entity) {
    $violations = [];

    // TODO write some example validation for time
    if(0 == 0) {
      $violations[] = [
        'message' => "Please don't publish pages after midnight.",
        'cause' => 'allow_link' // send this cause if you want to ignore this violation when showing links
      ];
    }

    return $violations;
  }

  /**
   * Act on publish transition
   * TransitionId_switch
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */

  public function publish_switch(ContentEntityInterface $entity) {


  }

  /**
   * Act on Insert
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function insert(ContentEntityInterface $entity) {

  }
}
