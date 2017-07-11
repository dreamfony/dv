<?php

namespace Drupal\activity_viewer\Plugin\ExtraField\FieldFormatter;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldFormatterBase;
use Drupal\content_moderation\Entity\ContentModerationState;
use Drupal\workflows\Entity\Workflow;

/**
 * Example Extra field formatter.
 *
 * @ExtraFieldFormatter(
 *   id = "activity_status",
 *   label = @Translation("Activity Status"),
 *   bundles = {
 *     "activity.*"
 *   },
 *   weight = -30,
 *   visible = true
 * )
 */
class ActivityStatus extends ExtraFieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {

    /** @var \Drupal\content_moderation\ModerationInformationInterface $moderationInfo */
    $moderationInfo = \Drupal::service('content_moderation.moderation_information');

    /** @var Workflow $workflow */
    $workflow = $moderationInfo->getWorkflowForEntity($entity);

    $state = $workflow->getState($entity->moderation_state->value);

    $elements = ['#markup' => $state->label()];

    return $elements;
  }

}
