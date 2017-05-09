<?php

namespace Drupal\dmt_group_comments\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;
use Drupal\Core\Entity\FieldableEntityInterface;


/**
 * Class GroupComments.
 *
 * @package Drupal\dmt_group_comments\Controller
 */
class GroupComments extends ControllerBase {

  /**
   * Group Comments view.
   *
   * @param \Drupal\group\Entity\Group $group
   * @param string $view_mode
   * @return array
   */
  public function groupCommentsView(Group $group, $view_mode = 'comments') {
    $page = \Drupal::entityTypeManager()
      ->getViewBuilder($group->getEntityTypeId())
      ->view($group, $view_mode);

    //$page['#pre_render'][] = [$this, 'buildTitle'];
    $page['#entity_type'] = $group->getEntityTypeId();
    $page['#' . $page['#entity_type']] = $group;

    return $page;
  }

  /**
   * Pre-render callback to build the page title.
   *
   * @param array $page
   *   A page render array.
   *
   * @return array
   *   The changed page render array.
   */
  public function buildTitle(array $page) {
    $entity_type = $page['#entity_type'];
    $entity = $page['#' . $entity_type];
    // If the entity's label is rendered using a field formatter, set the
    // rendered title field formatter as the page title instead of the default
    // plain text title. This allows attributes set on the field to propagate
    // correctly (e.g. RDFa, in-place editing).
    if ($entity instanceof FieldableEntityInterface) {
      $label_field = $entity->getEntityType()->getKey('label');
      if (isset($page[$label_field])) {
        $page['#title'] = $this->renderer->render($page[$label_field]);
      }
    }
    return $page;
  }

}
