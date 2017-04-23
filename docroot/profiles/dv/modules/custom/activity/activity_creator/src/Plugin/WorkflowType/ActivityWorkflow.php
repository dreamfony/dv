<?php

namespace Drupal\activity_creator\Plugin\WorkflowType;

use Drupal\content_moderation\Plugin\WorkflowType\ContentModeration;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\workflows\WorkflowInterface;


/**
 * Attaches workflows to content entity types and their bundles.
 *
 * @WorkflowType(
 *   id = "activity_workflow",
 *   label = @Translation("Activity Workflow"),
 *   required_states = {
 *     "canceled",
 *     "pending",
 *     "delivery_error",
 *     "sent",
 *     "seen",
 *   },
 * )
 */
class ActivityWorkflow extends ContentModeration implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function initializeWorkflow(WorkflowInterface $workflow) {
    $workflow
      ->addState('canceled', $this->t('Canceled'))
      ->setStateWeight('canceled', -10)
      ->addState('pending', $this->t('Pending'))
      ->setStateWeight('pending', -5)
      ->addState('delivery_error', $this->t('Delivery Error'))
      ->setStateWeight('delivery_error', 0)
      ->addState('sent', $this->t('Sent'))
      ->setStateWeight('sent', 5)
      ->addState('seen', $this->t('Seen'))
      ->setStateWeight('seen', 10)
      ->addTransition('erred', $this->t('Erred'), ['pending'], 'delivery_error')
      ->addTransition('send', $this->t('Send'), ['pending'], 'sent')
      ->addTransition('cancel', $this->t('Cancel'), ['pending', 'sent', 'seen', 'delivery_error'], 'canceled')
      ->addTransition('see', $this->t('See'), ['sent'], 'seen');
    return $workflow;
  }

  /**
   * {@inheritdoc}
   */
  public function checkWorkflowAccess(WorkflowInterface $entity, $operation, AccountInterface $account) {
    if ($operation === 'view') {
      return AccessResult::allowedIfHasPermission($account, 'view activity moderation');
    }
    return parent::checkWorkflowAccess($entity, $operation, $account);
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    // This plugin does not store anything per transition.
    return [
      'states' => [
        'canceled' => [
          'published' => FALSE,
          'default_revision' => TRUE,
        ],
        'pending' => [
          'published' => TRUE,
          'default_revision' => TRUE,
        ],
        'delivery_error' => [
          'published' => TRUE,
          'default_revision' => TRUE,
        ],
        'sent' => [
          'published' => TRUE,
          'default_revision' => TRUE,
        ],
        'seen' => [
          'published' => TRUE,
          'default_revision' => TRUE,
        ],
      ],
      'entity_types' => [],
    ];
  }

}
