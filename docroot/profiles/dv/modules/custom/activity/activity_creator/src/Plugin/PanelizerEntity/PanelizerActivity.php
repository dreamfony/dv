<?php

namespace Drupal\activity_creator\Plugin\PanelizerEntity;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\panelizer\Plugin\PanelizerEntityBase;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * Panelizer entity plugin for integrating with groups.
 *
 * @PanelizerEntity("activity")
 */
class PanelizerActivity extends PanelizerEntityBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultDisplay(EntityViewDisplayInterface $display, $bundle, $view_mode) {
    $panels_display = parent::getDefaultDisplay($display, $bundle, $view_mode)
      ->setPageTitle('');

    return $panels_display;
  }

  /**
   * {@inheritdoc}
   */
  public function alterBuild(array &$build, EntityInterface $entity, PanelsDisplayVariant $panels_display, $view_mode) {
    /** @var $entity \Drupal\activity_creator\Entity\Activity */
    parent::alterBuild($build, $entity, $panels_display, $view_mode);

    if ($entity->id()) {
      $build['#contextual_links']['activity'] = [
        'route_parameters' => ['activity' => $entity->id()],
        'metadata' => ['changed' => $entity->getChangedTime()],
      ];
    }
  }

}
