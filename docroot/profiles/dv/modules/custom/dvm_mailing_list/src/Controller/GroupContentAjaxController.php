<?php

namespace Drupal\dvm_mailing_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityFormBuilder;
use Drupal\group\Entity\GroupContent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Block\BlockManager;

class GroupContentAjaxController extends ControllerBase {

  /**
   * @var \Drupal\Core\Entity\EntityFormBuilder
   */
  protected $entityFormBuilder;

  /**
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * GroupContentAjaxController constructor.
   *
   * @param \Drupal\Core\Entity\EntityFormBuilder $entity_form_builder
   * @param \Drupal\Core\Block\BlockManager $block_manager
   */
  public function __construct(EntityFormBuilder $entity_form_builder, BlockManager $block_manager) {
    $this->entityFormBuilder = $entity_form_builder;
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.form_builder'),
      $container->get('plugin.manager.block')
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
    $plugin_block = $this->blockManager->createInstance('views_block:mailing_list_organisations-block_1');
    if ($plugin_block->access(\Drupal::currentUser())) {
      return $plugin_block->build();
    }
    return FALSE;
  }

}
