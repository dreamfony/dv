<?php

namespace Drupal\dmt_mailing_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityFormBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\group\Entity\GroupContent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\ContentEntityInterface;

class ContentAjaxController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityFormBuilder definition.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilder
   */
  protected $entityFormBuilder;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * ContentAjaxController constructor.
   *
   * @param \Drupal\Core\Entity\EntityFormBuilder $entity_form_builder
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   */
  public function __construct(EntityFormBuilder $entity_form_builder, EntityTypeManager $entity_type_manager) {
    $this->entityFormBuilder = $entity_form_builder;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.form_builder'),
      $container->get('entity_type.manager')
    );
  }


  /**
   * Sends back a form to edit content.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function editForm(EntityInterface $node) {
    $form = $this->entityFormBuilder->getForm($node);

    $response = new AjaxResponse();
    $selector = '.content-view-' . $node->id() . ' .node';
    $response->addCommand(new ReplaceCommand($selector, $form));
    return $response;
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $node
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function delete(ContentEntityInterface $node) {
    $group_contents = GroupContent::loadByEntity($node);

    foreach ($group_contents as $group_content) {
      /** @var GroupContent $group_content */
      $group_content->delete();
    }

    $node->delete();

    $response = new AjaxResponse();
    $selector = '.content-view-' . $node->id() . ' .node';
    $response->addCommand(new ReplaceCommand($selector, ''));
    return $response;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $node
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function cancel(EntityInterface $node) {

    $view_builder = $this->entityTypeManager->getViewBuilder('node');
    $renderable_entity = $view_builder->view($node, 'mailing_list_item');

    $response = new AjaxResponse();
    $selector = '.content-view-' . $node->id();
    $response->addCommand(new ReplaceCommand($selector, $renderable_entity));
    return $response;
  }
}
