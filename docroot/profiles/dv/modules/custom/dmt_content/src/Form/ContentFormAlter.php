<?php

namespace Drupal\dmt_content\Form;

use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Form\FormState;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\node\Entity\Node;


class ContentFormAlter {

  /**
   * Rebuild the form.
   */
  public static function ajaxFormEntityNodeFormSubmit($form, FormState &$form_state) {
    /** @var Entity $entity */
    $entity = $form_state->getBuildInfo()['callback_object']->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    if (isset($form['#isNew'])) {
      $new_entity = \Drupal::entityTypeManager()
        ->getStorage($entity_type)
        ->create(['type' => $bundle]);
      $form_state->getBuildInfo()['callback_object']->setEntity($new_entity);
    }

    $userInput = $form_state->getUserInput();
    $keys = $form_state->getCleanValueKeys();
    $newInputArray = [];
    foreach ($keys as $key) {
      if ($key == "op")  continue;
      $newInputArray[$key] = $userInput[$key];
    }

    $newInputArray['entity'] = $entity;

    $form_state->setUserInput($newInputArray);
    $form_state->setRebuild(true);
  }


  /**
   * Ajax callback to handle special ajax form entity magic.
   */
  public static function ajaxFormEntityCallback(&$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // If errors, returns the form with errors and messages.
    if ($form_state->hasAnyErrors()) {
      return $form;
    }
    // Else show the result.
    else {

      // get rendered entity
      $userInputs = $form_state->getUserInput();
      /** @var Node $entity */
      $entity = $userInputs['entity'];
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
      $renderable_entity = $view_builder->view($entity, 'mailing_list_item');
      $content_view_class = '.content-view-' . $entity->id();

      // create ajax response
      $response = new AjaxResponse();

      // Get messages even if not shown.
      $status_messages = array('#type' => 'status_messages');
      $message = array(
        '#markup' => \Drupal::service('renderer')
          ->renderRoot($status_messages)
      );

      // if entity is new
      if (isset($form['#isNew'])) {

        $group = \Drupal::routeMatch()->getParameter('group');

        // add node to created group
        $group->addContent($entity, 'group_node:' . $entity->bundle());

        // Remove old messages.
        /// @todo: Figure out how we are going to deal with messages ... also why is this breaking contents form ?
        //$response->addCommand(new RemoveCommand('.alert'));
        //$response->addCommand(new ReplaceCommand('.alert', $message));

        // replace form with empty form
        $response->addCommand(new ReplaceCommand('.node-content-form', $form));

        // remove view-empty
        $response->addCommand(new RemoveCommand('.view-mailing-list-items .view-empty'));

        // append entity to a view
        $response->addCommand(new PrependCommand('.view-mailing-list-items .view-content', $renderable_entity));
      }
      else {
        // replace form with edited entity
        $response->addCommand(new ReplaceCommand($content_view_class, $renderable_entity));
      }

      return $response;
    }
  }
}
