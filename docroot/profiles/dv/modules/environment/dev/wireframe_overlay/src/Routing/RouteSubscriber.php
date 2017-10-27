<?php

namespace Drupal\wireframe_overlay\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteProvider;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * Class RouteSubscriber.
 *
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Routing\RouteProvider
   */
  protected $routeProvider;

  /**
   * RouteSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(ConfigFactoryInterface $configFactory, RouteProvider $routeProvider) {
    $this->configFactory = $configFactory;
    $this->routeProvider = $routeProvider;
  }

  /**
   * @inheritdoc
   */
  protected function alterRoutes(RouteCollection $collection) {
    // TODO: Implement alterRoutes() method.
  }

  /**
   * @return \Symfony\Component\Routing\RouteCollection
   */
  public function routes() {
    $wireframe_overlays = $this->configFactory->listAll('wireframe_overlay');

    $route_collection = new RouteCollection();

    if(count($wireframe_overlays) == 0) {
      // if there are no wirefame_overlays configured we return empty route collection
      return $route_collection;
    }

    foreach ($wireframe_overlays as $wireframe_overlay) {

      /** @var \Drupal\Core\Config\ImmutableConfig $wireframe_overlay_config */
      $wireframe_overlay_config = $this->configFactory->get($wireframe_overlay);

      /** @var RouteCollection $existing_routes */
      $existing_routes = $this->routeProvider->getRoutesByPattern($wireframe_overlay_config->get('route'));

      if($existing_routes->count() > 0) {

        $real_route_exists = FALSE;

        foreach ($existing_routes as $existing_route) {
          if($existing_route->getDefault('_controller') == '\\Drupal\\wireframe_overlay\\Controller\\Wireframe::content') {
            continue;
          } else {
            $real_route_exists = TRUE;
          }
        }

        if($real_route_exists) {
          continue;
        }
      }

      $route = new Route(
        // Path to attach this route to:
        $wireframe_overlay_config->get('route'),
        // Route defaults:
        [
          '_controller' => '\Drupal\wireframe_overlay\Controller\Wireframe::content',
          '_title' => $wireframe_overlay_config->get('label')
        ],
        // Route requirements:
        [
          '_permission'  => 'access content',
        ]
      );

      // Add the route under the name 'example.content'.
      $route_collection->add($wireframe_overlay, $route);
    }

    return $route_collection;
  }
}
