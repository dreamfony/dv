<?php

namespace Drupal\dvm_mailing_list\Plugin\ModerationStateMachine;

use Drupal\content_moderation\ModerationInformation;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\moderation_state_machine\ModerationStateMachineBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dvm_mailing_list\MailingList;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\content_moderation\StateTransitionValidation;


/**
 * Mailing List Activity State Machine.
 *
 * @ModerationStateMachine(
 *  id = "dvm_mailing_mailing_list_activity",
 *  entity_type = "activity",
 *  entity_bundle = "mailing_list_activity",
 *  weight = 2,
 *  label = @Translation("Mailing List Activity"),
 * )
 */
class MailingListActivityStateMachine extends ModerationStateMachineBase implements ContainerFactoryPluginInterface {

  protected $mailingList;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateTransitionValidation $stateTransitionValidation, ModerationInformation $moderationInformation, AccountInterface $account, MailingList $mailingList) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $stateTransitionValidation, $moderationInformation, $account);

    $this->mailingList = $mailingList;
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
      $container->get('current_user'),
      $container->get('dvm_mailing_list.mailing_list')
    );
  }

  /**
   * On insertion of mailing_list_activity switch display mode.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function insert(ContentEntityInterface $entity) {
    $group_id = $entity->field_activity_mailing_list->target_id;
    if (!empty($group_id)) {
      if ($this->mailingList->checkActivitiesCreated($group_id)) {
        $this->mailingList->switchDisplay($group_id);
      }
    }
  }

}
