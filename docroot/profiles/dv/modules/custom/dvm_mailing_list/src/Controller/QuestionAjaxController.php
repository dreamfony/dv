<?php

namespace Drupal\dvm_mailing_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityFormBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\NodeInterface;

class QuestionAjaxController extends ControllerBase {

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
   * Sends back a form to edit question.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function editForm(EntityInterface $node) {
    $form = $this->entity_form_builder->getForm($node);

    $response = new AjaxResponse();
    $selector = '.question-view-' . $node->id() . ' .node';
    $response->addCommand(new ReplaceCommand($selector, $form));
    return $response;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $node
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function delete(EntityInterface $node) {
    $node->delete();

    $response = new AjaxResponse();
    $selector = '.question-view-' . $node->id() . ' .node';
    $response->addCommand(new ReplaceCommand($selector, ''));
    return $response;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $node
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function cancel(EntityInterface $node) {

    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
    $renderable_entity = $view_builder->view($node, 'full');

    $response = new AjaxResponse();
    $selector = '.question-view-' . $node->id();
    $response->addCommand(new ReplaceCommand($selector, $renderable_entity));
    return $response;
  }
}
