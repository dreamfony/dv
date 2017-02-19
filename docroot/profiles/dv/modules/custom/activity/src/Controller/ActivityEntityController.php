<?php

namespace Drupal\activity\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\activity\Entity\ActivityEntityInterface;

/**
 * Class ActivityEntityController.
 *
 *  Returns responses for Activity routes.
 *
 * @package Drupal\activity\Controller
 */
class ActivityEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Activity  revision.
   *
   * @param int $activity_entity_revision
   *   The Activity  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($activity_entity_revision) {
    $activity_entity = $this->entityManager()->getStorage('activity_entity')->loadRevision($activity_entity_revision);
    $view_builder = $this->entityManager()->getViewBuilder('activity_entity');

    return $view_builder->view($activity_entity);
  }

  /**
   * Page title callback for a Activity  revision.
   *
   * @param int $activity_entity_revision
   *   The Activity  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($activity_entity_revision) {
    $activity_entity = $this->entityManager()->getStorage('activity_entity')->loadRevision($activity_entity_revision);
    return $this->t('Revision of %title from %date', array('%title' => $activity_entity->label(), '%date' => format_date($activity_entity->getRevisionCreationTime())));
  }

  /**
   * Generates an overview table of older revisions of a Activity .
   *
   * @param \Drupal\activity\Entity\ActivityEntityInterface $activity_entity
   *   A Activity  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ActivityEntityInterface $activity_entity) {
    $account = $this->currentUser();
    $langcode = $activity_entity->language()->getId();
    $langname = $activity_entity->language()->getName();
    $languages = $activity_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $activity_entity_storage = $this->entityManager()->getStorage('activity_entity');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $activity_entity->label()]) : $this->t('Revisions for %title', ['%title' => $activity_entity->label()]);
    $header = array($this->t('Revision'), $this->t('Operations'));

    $revert_permission = (($account->hasPermission("revert all activity revisions") || $account->hasPermission('administer activity entities')));
    $delete_permission = (($account->hasPermission("delete all activity revisions") || $account->hasPermission('administer activity entities')));

    $rows = array();

    $vids = $activity_entity_storage->revisionIds($activity_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\activity\ActivityEntityInterface $revision */
      $revision = $activity_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->revision_timestamp->value, 'short');
        if ($vid != $activity_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.activity_entity.revision', ['activity_entity' => $activity_entity->id(), 'activity_entity_revision' => $vid]));
        }
        else {
          $link = $activity_entity->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->revision_log_message->value, '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('activity_entity.revision_revert_translation_confirm', ['activity_entity' => $activity_entity->id(), 'activity_entity_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('activity_entity.revision_revert_confirm', ['activity_entity' => $activity_entity->id(), 'activity_entity_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('activity_entity.revision_delete_confirm', ['activity_entity' => $activity_entity->id(), 'activity_entity_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['activity_entity_revisions_table'] = array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    );

    return $build;
  }

}
