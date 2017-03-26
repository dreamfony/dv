<?php

namespace Drupal\dvm_mailing_list\Plugin\views\field;

use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\Group;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Field handler to delete group content.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("group_content_ajax_delete_link")
 */
class GroupContentAjaxDeleteLink extends FieldPluginBase {

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var GroupContent $group_content */
    $group_content = $values->_entity;
    /** @var Group $group */
    $group = $group_content->getGroup();

    if ($group->bundle() == 'mailing_list' && $group->access('update')) {

      $url = Url::fromRoute('dvm_mailing_list.ajax_organisation_delete_link', [
        'group' => $group->id(),
        'group_content' => $group_content->id()
      ]);
      $project_link = Link::fromTextAndUrl(t('Delete'), $url);

      $project_link = $project_link->toRenderable();
      // If you need some attributes.
      $project_link['#attributes'] = array('class' => array('use-ajax'));

      return render($project_link);
    }


  }
}
