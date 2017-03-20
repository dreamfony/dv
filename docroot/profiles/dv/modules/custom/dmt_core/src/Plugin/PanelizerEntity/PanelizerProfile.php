<?php

namespace Drupal\dmt_core\Plugin\PanelizerEntity;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\panelizer\Plugin\PanelizerEntityBase;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * Panelizer entity plugin for integrating with profile.
 *
 * @PanelizerEntity("profile")
 */
class PanelizerProfile extends PanelizerEntityBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultDisplay(EntityViewDisplayInterface $display, $bundle, $view_mode) {
    $panels_display = parent::getDefaultDisplay($display, $bundle, $view_mode)
      ->setPageTitle('[profile:field_org_title]');

    // Remove the 'title' block because it's covered already.
    foreach ($panels_display->getRegionAssignments() as $region => $blocks) {
      /** @var \Drupal\Core\Block\BlockPluginInterface[] $blocks */
      foreach ($blocks as $block_id => $block) {
        if ($block->getPluginId() == 'entity_field:profile:title') {
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
      $build['#contextual_links']['profile'] = [
        'route_parameters' => ['profile' => $entity->id()],
        'metadata' => ['changed' => $entity->getChangedTime()],
      ];
    }
  }

}
