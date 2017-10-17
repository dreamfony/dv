<?php

namespace Drupal\dmt_mailing_list\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class MailingListSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $route = $collection->get('entity.group.edit_form');

    if ($route) {
      $route->setRequirement('_mailing_list_edit_access', 'FALSE');
    }
  }

}
