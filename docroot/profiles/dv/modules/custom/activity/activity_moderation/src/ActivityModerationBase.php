<?php

/**
 * @file
 * Contains \Drupal\activity_creator\Plugin\ActivityActionBase.
 */

namespace Drupal\activity_moderation;

use Drupal\Component\Plugin\PluginBase;
use Drupal\activity_creator\Plugin\Type\ActivityActionManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\activity_creator\Entity\Activity;

/**
 * Base class for Activity moderation plugins.
 */
class ActivityModerationBase extends PluginBase implements ActivityModerationInterface, ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\activity_creator\Plugin\Type\ActivityActionManager
   */
  protected $activityActionManager;

  /**
   * Constructs a ActivityModerationBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\activity_creator\Plugin\Type\ActivityActionManager $activity_action_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ActivityActionManager $activity_action_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->activityActionManager = $activity_action_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.activity_action_processor')
    );
  }

  /**
   * @inheritdoc
   */
  public function test() {
    $test = $this->getPluginDefinition();
  }

  /**
   * Create Moderation Activity
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public function createModerationActivity(EntityInterface $entity) {
    $create_action = $this->activityActionManager->createInstance('moderation_action');
    $create_action->create($entity);
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public function closeModerationActivity(EntityInterface $entity) {
    $message_type_id = $this->getPluginDefinition()['message_type_id'];
    $activity_ids = $this->getRelatedActivities($entity->id(), ACTIVITY_STATUS_PENDING);
//    TODO Move this to queue one day.
    foreach ($activity_ids as $activity_id) {
      $activity = Activity::load($activity_id);
      if ($activity->getMessageTypeId() == $message_type_id) {
        /** @var Activity $activity */
        $activity->setModerationState(ACTIVITY_STATUS_RESOLVED);
        $activity->save();
      }
    }
  }

  /**
   * We actually never know which event exactly created which activity.
   * Probably in more complex scenario we could create event entity and use UUID
   *
   * @param $entity_id
   * @param $status
   * @param string $bundle
   * @return array|int
   */
  private function getRelatedActivities($entity_id, $status) {
    $query = \Drupal::entityQuery('activity')
      ->condition('field_activity_entity.target_id', $entity_id)
      ->condition('type', 'moderation_activity');

    if ($status) {
      /** @see activity_creator_query_cm_states_alter */
      $query->addTag('cm_states');
      $query->addMetaData('states', [$status]);
    }

    $activities = $query->execute();

    return $activities;
  }
}

