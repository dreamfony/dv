<?php

namespace Drupal\wireframe_overlay\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WireframeOverlayForm.
 */
class WireframeOverlayForm extends EntityForm {

  /**
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routeBuilder;

  /**
   * WireframeOverlayForm constructor.
   *
   * @param \Drupal\Core\Routing\RouteBuilderInterface $routeBuilder
   */
  public function __construct(RouteBuilderInterface $routeBuilder) {
    $this->routeBuilder = $routeBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.builder')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $wireframe_overlay = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $wireframe_overlay->label(),
      '#description' => $this->t("Label for the Wireframe overlay."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $wireframe_overlay->id(),
      '#machine_name' => [
        'exists' => '\Drupal\wireframe_overlay\Entity\WireframeOverlay::load',
      ],
      '#disabled' => !$wireframe_overlay->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    $form['route'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Route'),
      '#maxlength' => 255,
      '#default_value' => isset($wireframe_overlay->route) ? $wireframe_overlay->route : '',
      '#description' => $this->t("Route for the Wireframe overlay."),
      '#required' => TRUE,
    ];

    $form['image'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Image'),
      '#maxlength' => 255,
      '#default_value' => isset($wireframe_overlay->image) ? $wireframe_overlay->image : '',
      '#description' => $this->t("Image for the Wireframe overlay."),
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => isset($wireframe_overlay->description) ? $wireframe_overlay->description : '',
      '#description' => $this->t("Description for the Wireframe overlay."),
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $wireframe_overlay = $this->entity;
    $status = $wireframe_overlay->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Wireframe overlay.', [
          '%label' => $wireframe_overlay->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Wireframe overlay.', [
          '%label' => $wireframe_overlay->label(),
        ]));
    }

    $this->routeBuilder->rebuild();

    $form_state->setRedirectUrl($wireframe_overlay->toUrl('collection'));
  }

}
