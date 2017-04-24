<?php

/**
 * @file
 * Contains \Drupal\activity_logger\Plugin\QueueWorker\MessageQueueCreator.
 */

namespace Drupal\activity_logger\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\activity_creator\Plugin\ActivityActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * A report worker.
 *
 * @QueueWorker(
 *   id = "activity_logger_message",
 *   title = @Translation("Process 1 activity_logger_message."),
 *   cron = {"time" = 60}
 * )
 *
 * This QueueWorker is responsible for creating message items from the queue
 */
class MessageQueueCreator extends MessageQueueBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * MessageQueueCreator constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {

    // First make sure it's an actual entity.
    if ($entity = $this->entityTypeManager->getStorage($data['entity_type_id'])->load($data['entity_id'])) {
      $timestamp = $entity->getCreatedTime();
      // Current time.
      $now = time();
      $diff = $now - $timestamp;

      // Items must be at least 5 seconds old.
      if ($diff <= 5) {
        // Wait for 100 milliseconds.
        // We don't want to flood the DB with unprocessable queue items.
        usleep(100000);
        $queue = \Drupal::queue('activity_logger_message');
        $queue->createItem($data);
      }
      else {
        $activity_logger_factory = \Drupal::service('plugin.manager.activity_action_processor');
        // Trigger the create action for entities.
        /** @var ActivityActionBase $create_action */
        $create_action = $activity_logger_factory->createInstance('create_activity_action');
        $create_action->createMessage($entity, $data);
      }
    }
  }

}
