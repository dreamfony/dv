<?php

namespace Drupal\dmt_mailing_list_activity\Plugin\ModerationStateMachine;

use Drupal\activity_creator\Plugin\Type\ActivityActionManager;
use Drupal\activity_moderation\Plugin\Type\ActivityModerationManager;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dmt_mailing_list_activity\MailingListActivity;
use Drupal\moderation_state_machine\ModerationStateMachineBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\group\GroupMembershipLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group\Entity\GroupContent;

/**
* Provides a 'ModerationAction' activity action.
*
* @ModerationStateMachine(
*  id = "delivery_error",
*  label = @Translation("Mailing List Approve Sending Transition"),
*  transition_id = "delivery_error",
*  entity_type = "activity",
*  entity_bundle = "mailing_list_activity",
* )
*/
class DeliveryErrorTransition extends ModerationStateMachineBase {

  public function switchTransition(ContentEntityInterface $entity) {

  }
}
