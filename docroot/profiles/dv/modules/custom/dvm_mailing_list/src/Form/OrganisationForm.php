<?php

namespace Drupal\dvm_mailing_list\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Block\BlockManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dvm_mailing_list\MailingList;

/**
 * Form for editing Persistent Login module settings.
 */
class OrganisationForm extends FormBase {

  /**
   * @var \Drupal\dvm_mailing_list\MailingList $mailingList
   */
  protected $mailingList;

  /**
   * OrganisationForm constructor.
   *
   * @param \Drupal\dvm_mailing_list\MailingList $mailing_list
   */
  public function __construct(MailingList $mailing_list) {
    $this->mailingList = $mailing_list;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dvm_mailing_list.mailing_list')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'organisation_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $ajax_id = 'organisation_form';

    $form['#attributes']['class'][] = $ajax_id;

    // Ajax settings of the buttons.
    $ajax_settings = array(
      'callback' => '\Drupal\dvm_mailing_list\Form\OrganisationForm::ajaxFormCallback',
      'wrapper' => $ajax_id,
      'effect' => 'fade',
    );

    $form['group'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'group',
      '#title' => t('Recipients'),
      '#description' => t('Select a recipient.'),
      '#tags' => TRUE,
      '#selection_settings' => array(
        'target_bundles' => array('organisation', 'area_of_activity'),
      ),
    ];

    $group = \Drupal::routeMatch()->getParameter('group');
    $form['#mailing_list_id'] = $group->id();

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Add Recipient'),
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
    $gids = $form_state->getValue('group');
    $mailing_list_id = $form['#mailing_list_id'];

    $this->mailingList->addRecipients($gids, $mailing_list_id);
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

      // replace form with empty one
      $form['group']['#value'] = NULL;
      $response->addCommand(new ReplaceCommand('.organisation_form', $form));

      // replace view
      $view = self::getMailingListOrganisationsView();
      $response->addCommand(new ReplaceCommand('.view-mailing-list-organisations', $view));

      return $response;
    }
  }

  static function getMailingListOrganisationsView() {
    // replace items view
    /** @var BlockManager $block_manager */
    $block_manager = \Drupal::service('plugin.manager.block');

    $plugin_block = $block_manager->createInstance('views_block:mailing_list_organisations-block_1');
    if ($plugin_block->access(\Drupal::currentUser())) {
      return $plugin_block->build();
    }
    return FALSE;
  }

}
