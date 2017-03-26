<?php

namespace Drupal\dvm_mailing_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityFormBuilder;
use Drupal\Core\Entity\EntityManager;
use Drupal\group\Entity\GroupContent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Block\BlockManager;
use Drupal\views\Plugin\Block\ViewsBlock;

class GroupContentAjaxController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityFormBuilder definition.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilder
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entity_form_builder;
  protected $entity_manager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityFormBuilder $entity_form_builder, EntityManager $entity_manager) {
    $this->entity_form_builder = $entity_form_builder;
    $this->entity_manager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.form_builder'),
      $container->get('entity.manager')
    );
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $group_content
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function delete(ContentEntityInterface $group, ContentEntityInterface $group_content) {
    /** @var GroupContent $group_content */
    $group_content->delete();

    $response = new AjaxResponse();
    $selector = '.view-mailing-list-organisations';

    $view = $this->getMailingListOrganisationsView();
    $response->addCommand(new ReplaceCommand($selector, $view));
    return $response;
  }

  public function getMailingListOrganisationsView() {
    // replace items view
    /** @var BlockManager $block_manager */
    $block_manager = \Drupal::service('plugin.manager.block');
    $config = [];
    /** @var ViewsBlock $plugin_block */
    $plugin_block = $block_manager->createInstance('views_block:mailing_list_organisations-block_1', $config);
    if ($plugin_block->access(\Drupal::currentUser())) {
      return $plugin_block->build();
    }
    return FALSE;
  }

}
