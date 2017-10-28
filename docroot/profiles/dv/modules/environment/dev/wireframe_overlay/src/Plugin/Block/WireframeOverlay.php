<?php

namespace Drupal\wireframe_overlay\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Path\AliasManager;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactory;
use Unish\outputFormatUnitCase;

/**
 * Provides a 'WireframeOverlay' block.
 *
 * @Block(
 *  id = "wireframe_overlay",
 *  admin_label = @Translation("Wireframe overlay"),
 * )
 */
class WireframeOverlay extends BlockBase implements ContainerFactoryPluginInterface {

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
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        ConfigFactory $config_factory,
        CurrentPathStack $currentPathStack,
        AliasManager $aliasManager

  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->currentPath = $currentPathStack;
    $this->aliasManager = $aliasManager;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('path.current'),
      $container->get('path.alias_manager')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $wireframe_overlays = $this->configFactory->listAll('wireframe_overlay');

    if(count($wireframe_overlays) == 0) {
      // if there are no wirefame_overlays configured we return empty route collection
      return $build;
    }

    $current_path = $this->currentPath->getPath();
    $path_alias = $this->aliasManager->getAliasByPath($current_path);

    $wireframe_overlay_config_match = (object) [];
    foreach ($wireframe_overlays as $wireframe_overlay) {
      $wireframe_overlay_config = $this->configFactory->get($wireframe_overlay);
      $wireframe_overlay_route = '/' . $wireframe_overlay_config->get('route');
      if($wireframe_overlay_route == $path_alias) {
        $wireframe_overlay_config_match = $wireframe_overlay_config;
      }
    }

    $build = [
      '#theme' => 'wireframe_overlay',
      '#toggle' => t('Toggle'),
      '#label' => $wireframe_overlay_config_match->get('label'),
      '#image' => $wireframe_overlay_config_match->get('image'),
      '#description' => $wireframe_overlay_config_match->get('description'),
    ];

    return $build;
  }

}
