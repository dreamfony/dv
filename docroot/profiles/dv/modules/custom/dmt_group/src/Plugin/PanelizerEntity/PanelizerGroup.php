<?php

namespace Drupal\dmt_group\Plugin\PanelizerEntity;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\panelizer\Plugin\PanelizerEntityBase;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * Panelizer entity plugin for integrating with groups.
 *
 * @PanelizerEntity("group")
 */
class PanelizerGroup extends PanelizerEntityBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultDisplay(EntityViewDisplayInterface $display, $bundle, $view_mode) {
    $panels_display = parent::getDefaultDisplay($display, $bundle, $view_mode)
      ->setPageTitle('[group:title]');

    // Remove the 'title' block because it's covered already.
    foreach ($panels_display->getRegionAssignments() as $region => $blocks) {
      /** @var \Drupal\Core\Block\BlockPluginInterface[] $blocks */
      foreach ($blocks as $block_id => $block) {
        if ($block->getPluginId() == 'entity_field:group:title') {
          $panels_display->removeBlock($block_id);
        }
      }
    }

    return $panels_display;
  }

  /**
   * {@inheritdoc}
   */
  public function alterBuild(array &$build, EntityInterface $entity, PanelsDisplayVariant $panels_display, $view_mode) {
    /** @var $entity \Drupal\group\Entity\Group */
    parent::alterBuild($build, $entity, $panels_display, $view_mode);

    if ($entity->id()) {
      $build['#contextual_links']['group'] = [
        'route_parameters' => ['group' => $entity->id()],
        'metadata' => ['changed' => $entity->getChangedTime()],
      ];
    }
  }

}
