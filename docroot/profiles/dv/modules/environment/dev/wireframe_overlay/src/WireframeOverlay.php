<?php

namespace Drupal\wireframe_overlay;

use Drupal\Core\Path\AliasManager;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Config\ConfigFactory;

/**
 * Provides a 'WireframeOverlay' block.
 *
 */
class WireframeOverlay {

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * @var CurrentPathStack
   */
  protected $currentPath;

  /**
   * @var AliasManager
   */
  protected $aliasManager;

  /**
   * Constructs a new WireframeOverlay object.
   *
   */
  public function __construct(
        ConfigFactory $config_factory,
        CurrentPathStack $currentPathStack,
        AliasManager $aliasManager
  ) {
    $this->configFactory = $config_factory;
    $this->currentPath = $currentPathStack;
    $this->aliasManager = $aliasManager;
  }

  /**
   * {@inheritdoc}
   */
  public function overlayData() {
    $wireframe_overlays = $this->configFactory->listAll('wireframe_overlay');

    if(count($wireframe_overlays) == 0) {
      // if there are no wirefame_overlays configured we return empty route collection
      return FALSE;
    }

    $current_path = $this->currentPath->getPath();
    $path_alias = $this->aliasManager->getAliasByPath($current_path);

    foreach ($wireframe_overlays as $wireframe_overlay) {
      $wireframe_overlay_config = $this->configFactory->get($wireframe_overlay);
      $wireframe_overlay_route = '/' . $wireframe_overlay_config->get('route');
      if($wireframe_overlay_route == $path_alias) {
        $wireframe_overlay_config_match = $wireframe_overlay_config;

        $build = [
          'toggle' => t('Toggle'),
          'label' => $wireframe_overlay_config_match->get('label'),
          'image' => $wireframe_overlay_config_match->get('image'),
          'description' => $wireframe_overlay_config_match->get('description'),
        ];

        return $build;
      }
    }

    return FALSE;
  }

}
