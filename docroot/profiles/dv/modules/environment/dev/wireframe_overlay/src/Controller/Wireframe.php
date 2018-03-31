<?php

namespace Drupal\wireframe_overlay\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class Wireframe extends ControllerBase {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Wireframe constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }


  public function content(Request $request) {

    // config key is same as the route name set in RouteSubscriber
    $config_key = $request->attributes->get('_route');

    $config = $this->configFactory->get($config_key);

    $build = array(
      '#type' => 'markup',
      '#markup' => 'Check wireframes.',
    );

    return $build;
  }
}
