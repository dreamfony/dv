<?php

namespace Drupal\geonames;

use Drupal\Core\Config\ConfigFactory;

/**
 * Class GeoNamesFactory
 * @package Drupal\geonames
 */
class GeoNamesFactory {

  static function create( $config ) {

    /** @var ConfigFactory $config */
    $config = $config->get('geonames.config');

    $username = $config->get('username');
    $server = $config->get('server');

    $token = $config->get('token');
    $token = !empty($token) ? $token : null;

    return new GeoNames( $username, $server, $token );
  }

}