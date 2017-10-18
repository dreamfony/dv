<?php

namespace Drupal\dmt_mailing_list_recipients\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\dmt_mailing_list_recipients\Recipients;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for editing Persistent Login module settings.
 */
class RecipientsForm extends FormBase {

  /**
   * @var Recipients $recipients
   */
  protected $recipients;

  /**
   * @var CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * RecipientsForm constructor.
   * @param \Drupal\dmt_mailing_list_recipients\Recipients $recipients
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   */
  public function __construct(Recipients $recipients, CurrentRouteMatch $currentRouteMatch) {
    $this->recipients = $recipients;
    $this->currentRouteMatch = $currentRouteMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dmt_mailing_list_recipients.recipients'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'recipients_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $ajax_id = 'recipients_form';

    $form['#attributes']['class'][] = $ajax_id;

    // Ajax settings of the buttons.
    $ajax_settings = array(
      'callback' => '\Drupal\dmt_mailing_list_recipients\Form\RecipientsForm::ajaxFormCallback',
      'wrapper' => $ajax_id,
      'effect' => 'fade',
    );

    $form['group'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'group',
      '#attributes' => array(
        'placeholder' => t('Recipients'),
      ),
      '#description' => t('Select a recipient.'),
      '#tags' => TRUE,
      '#selection_settings' => array(
        'target_bundles' => array('organisation', 'area_of_activity'),
      ),
    ];

    $group = $this->currentRouteMatch->getParameter('group');
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

    $this->recipients->addRecipients($gids, $mailing_list_id);
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
      $response->addCommand(new ReplaceCommand('#recipients-form', $form));

      // replace view
      $view = self::getRecipientsView();
      $response->addCommand(new ReplaceCommand('.view-mailing-list-organisations', $view));

      return $response;
    }
  }

  static function getRecipientsView() {
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
