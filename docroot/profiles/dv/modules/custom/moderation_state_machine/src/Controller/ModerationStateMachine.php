<?php

namespace Drupal\moderation_state_machine\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\EventSubscriber\AjaxResponseSubscriber;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Class ModerationStateMachine
 * @package Drupal\moderation_state_machine\Controller
 */
class ModerationStateMachine extends ControllerBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * ContentAjaxController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   */
  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Switch method.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param $entity_type
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param string $view_mode
   * @param $state_id
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function switchState(Request $request, $entity_type, ContentEntityInterface $entity, $view_mode, $state_id) {
    $is_ajax = $this->isAjaxRequest($request);

    $entity->set('moderation_state', $state_id);

    /** @var \Drupal\Core\Entity\EntityConstraintViolationList $violations */
    // Validate the entity before saving.
    $violations = $entity->moderation_state->validate();
    if ($violations->count()) {
      foreach ($violations as $violation) {
        drupal_set_message($this->t('@message', [
          '@message' => $violation->getMessage()
        ]), 'error');
      }

      return new RedirectResponse($entity->toUrl()->toString(), 302);
    }

    $entity->save();

    if($is_ajax) {
      $view_builder = $this->entityTypeManager->getViewBuilder($entity_type);
      $renderable_entity = $view_builder->view($entity, $view_mode);

      $response = new AjaxResponse();
      $selector = '.msm-'. $entity->getEntityTypeId() . '-' . $entity->id();
      $response->addCommand(new ReplaceCommand($selector, $renderable_entity));
      return $response;
    }

    return new RedirectResponse($entity->toUrl()->toString(), 302);
  }

  /**
   * Check if Request is Ajax Request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param array $input
   * @return bool
   */
  private function isAjaxRequest(Request $request, $input = []) {
    $has_ajax_parameter = $request
      ->request
      ->has(AjaxResponseSubscriber::AJAX_REQUEST_PARAMETER);
    $has_ajax_input_parameter = !empty(
    $input[AjaxResponseSubscriber::AJAX_REQUEST_PARAMETER]
    );
    $has_ajax_format = $request
        ->query
        ->get(MainContentViewSubscriber::WRAPPER_FORMAT) == 'drupal_ajax';
    return $has_ajax_parameter || $has_ajax_input_parameter || $has_ajax_format;
  }

}
