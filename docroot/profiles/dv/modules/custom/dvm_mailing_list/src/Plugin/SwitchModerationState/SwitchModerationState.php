<?php

namespace Drupal\dvm_mailing_list\Plugin\SwitchModerationState;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\dmt_moderation\SwitchModerationStateBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dvm_mailing_list\MailingList;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\content_moderation\StateTransitionValidation;

/**
 * Provides a 'ModerationAction' activity action.
 *
 * @SwitchModerationState(
 *  id = "dvm_mailing_list",
 *  entity_type = "group",
 *  entity_bundle = "mailing_list",
 *  label = @Translation("Switch Moderation State"),
 * )
 */
class SwitchModerationState extends SwitchModerationStateBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\dvm_mailing_list\MailingList
   */
  protected $mailingList;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateTransitionValidation $stateTransitionValidation, MailingList $mailingList) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $stateTransitionValidation);

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
      $container->get('dvm_mailing_list.mailing_list')
    );
  }

  public function approve_sending_validate(ContentEntityInterface $entity, AccountInterface $account) {
    return $this->mailingList->allActivitiesCountValidate($entity);
  }

  public function approve_sending_switch(ContentEntityInterface $entity, AccountInterface $account) {
    $this->mailingList->approve($entity);
  }

  public function send_email_validate(ContentEntityInterface $entity, AccountInterface $account) {
    return $this->mailingList->allActivitiesCountValidate($entity);
  }

  public function send_email_switch(ContentEntityInterface $entity, AccountInterface $account) {
    $this->mailingList->approve($entity);
  }

}
