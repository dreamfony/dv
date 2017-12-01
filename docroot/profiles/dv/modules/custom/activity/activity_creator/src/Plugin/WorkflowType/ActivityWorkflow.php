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
 *     "pending"
 *   },
 * )
 */
class ActivityWorkflow extends ContentModeration implements ContainerFactoryPluginInterface {

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    // This plugin does not store anything per transition.
    return [
      'states' => [
        'canceled' => [
          'label' => 'Canceled',
          'published' => FALSE,
          'default_revision' => TRUE,
          'weight' => -10
        ],
        'pending' => [
          'label' => 'Pending',
          'published' => TRUE,
          'default_revision' => TRUE,
          'weight' => -5
        ],
        'delivery_error' => [
          'label' => 'Delivery Error',
          'published' => TRUE,
          'default_revision' => TRUE,
          'weight' => 0
        ],
        'sent' => [
          'label' => 'Sent',
          'published' => TRUE,
          'default_revision' => TRUE,
          'weight' => 5
        ],
        'seen' => [
          'label' => 'Seen',
          'published' => TRUE,
          'default_revision' => TRUE,
          'weight' => 10
        ],
      ],
      'transitions' => [
        'erred' => [
          'label' => 'Erred',
          'to' => 'delivery_error',
          'weight' => 0,
          'from' => [
            'pending'
          ],
        ],
        'send' => [
          'label' => 'Send',
          'to' => 'sent',
          'weight' => 1,
          'from' => [
            'pending'
          ],
        ],
        'cancel' => [
          'label' => 'Cancel',
          'to' => 'canceled',
          'weight' => 2,
          'from' => [
            'pending',
            'sent',
            'seen',
            'delivery_error'
          ],
        ],
        'see' => [
          'label' => 'See',
          'to' => 'seen',
          'weight' => 3,
          'from' => [
            'sent'
          ],
        ],
      ],
      'entity_types' => ['activity'],
    ];
  }

}
