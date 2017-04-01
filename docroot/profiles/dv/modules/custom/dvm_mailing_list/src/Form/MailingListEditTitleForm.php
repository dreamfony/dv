<?php

namespace Drupal\dvm_mailing_list\Form;

use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\group\Entity\Group;
use Drupal\Core\Block\BlockManager;
use Drupal\views\Plugin\Block\ViewsBlock;
use Drupal\user\Entity\User;
use Drupal\group\GroupMembership;
use Drupal\Core\Ajax\AppendCommand;


/**
 * Form for editing Persistent Login module settings.
 */
class MailingListEditTitleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_title_form';
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $group
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state, Group $group = null) {

    $ajax_id = 'edit_title_form';

    $form['#attributes']['class'][] = $ajax_id;

    // Ajax settings of the buttons.
    $ajax_settings = array(
      'callback' => '\Drupal\dvm_mailing_list\Form\MailingListEditTitleForm::ajaxFormCallback',
      'wrapper' => $ajax_id,
      'effect' => 'fade',
    );

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#description' => t('Enter an new title.'),
      '#default_value' => $group->label()
    ];

    $form['#mailing_list_id'] = $group->id();

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      '#ajax' => $ajax_settings
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // getMembership gids
    $title = $form_state->getValue('title');
    $mailing_list_group = Group::load($form['#mailing_list_id']);

    $mailing_list_group->set('label', $title);

    $mailing_list_group->save();
  }

  public function ajaxFormCallback(array &$form, FormStateInterface $form_state) {
    // If errors, returns the form with errors and messages.
    if ($form_state->hasAnyErrors()) {
      return $form;
    }
    // Else show the result.
    else {

      // create ajax response
      $response = new AjaxResponse();

      // Get messages even if not shown.
      $status_messages = array('#type' => 'status_messages');
      $message = array(
        '#markup' => \Drupal::service('renderer')
          ->renderRoot($status_messages)
      );

      /** @var BlockManager $block_manager */
      $block_manager = \Drupal::service('plugin.manager.block');
      $config = [];
      /** @var ViewsBlock $plugin_block */
      $plugin_block = $block_manager->createInstance('mailing_list_title_block', $config);

      // replace form with empty one
      $response->addCommand(new HtmlCommand('.block-mailing-list-title-block', $plugin_block->build()));

      $title = $form_state->getValue('title');

      $response->addCommand(new HtmlCommand('h1.page-header', $title));

      return $response;
    }
  }

}
