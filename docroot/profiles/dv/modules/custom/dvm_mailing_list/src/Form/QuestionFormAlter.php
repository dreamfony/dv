<?php

namespace Drupal\dvm_mailing_list\Form;

use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Form\FormState;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\BeforeCommand;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Entity\EntityManager;
use Drupal\node\Entity\Node;
use Drupal\views\Plugin\Block\ViewsBlock;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group\Entity\Group;

class QuestionFormAlter {

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

    // Clear user input.
    $input = $form_state->getUserInput();
    // We should not clear the system items from the user input.
    $clean_keys = $form_state->getCleanValueKeys();
    $clean_keys[] = 'ajax_page_state';
    foreach ($input as $key => $item) {
      if (!in_array($key, $clean_keys) && substr($key, 0, 1) !== '_') {
        unset($input[$key]);
      }
    }

    // Store new entity for display in the AJAX callback.
    $input['entity'] = $entity;
    $form_state->setUserInput($input);

    // Rebuild the form state values.
    $form_state->setRebuild();
    $form_state->setStorage([]);
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
      $renderable_entity = $view_builder->view($entity, 'full');
      $question_view_class = '.question-view-' . $entity->id();

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
        /// @todo: Figure out how we are going to deal with messages ... also why is this breaking questions form ?
        //$response->addCommand(new RemoveCommand('.alert'));
        //$response->addCommand(new ReplaceCommand('.alert', $message));

        // replace form with empty one
        $response->addCommand(new ReplaceCommand('.ajax-form-entity-node-question-new', $form));

        // remove view-empty
        $response->addCommand(new RemoveCommand('.view-mailing-list-items .view-empty'));

        // append entity to a view
        $response->addCommand(new PrependCommand('.view-mailing-list-items .view-content', $renderable_entity));
      }
      else {
        // replace form with edited entity
        $response->addCommand(new ReplaceCommand($question_view_class, $renderable_entity));
      }

      return $response;
    }
  }
}
