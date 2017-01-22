<?php

namespace Drupal\dv_positions\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Positions edit forms.
 *
 * @ingroup dv_positions
 */
class PositionsForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\dv_positions\Entity\Positions */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Positions.', [
          '%label' => $entity->id(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Positions.', [
          '%label' => $entity->id(),
        ]));
    }
    $form_state->setRedirect('entity.positions.canonical', ['positions' => $entity->id()]);
  }

}
