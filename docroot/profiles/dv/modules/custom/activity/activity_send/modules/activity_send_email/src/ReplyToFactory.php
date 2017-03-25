<?php

namespace Drupal\activity_send_email;

use Drupal\Core\Config\ConfigFactory;

/**
 * Class ReplyToFactory
 *
 * @package Drupal\activity_send_email
 */
class ReplyToFactory {

  static function create( $config ) {

    /** @var ConfigFactory $config */
    $config = $config->get('activity_send_email.config');

    $replyto = $config->get('replyto');
    $noreply = $config->get('noreply');
    $filterstring = $config->get('filterstring');

    return new ReplyTo($replyto, $noreply, $filterstring);
  }

}






