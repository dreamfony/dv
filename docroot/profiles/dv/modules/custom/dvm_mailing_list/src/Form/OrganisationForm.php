<?php

namespace Drupal\dvm_mailing_list\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\group\Entity\Group;
use Drupal\Core\Block\BlockManager;
use Drupal\views\Plugin\Block\ViewsBlock;
use Drupal\user\Entity\User;
use Drupal\group\GroupMembership;

/**
 * Form for editing Persistent Login module settings.
 */
class OrganisationForm extends FormBase {

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
      '#title' => t('Organisation Group'),
      '#description' => t('Select a group.'),
      '#tags' => TRUE,
      '#selection_settings' => array(
        'target_bundles' => array('organisation', 'area_of_activity'),
      ),
    ];

    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $form['#mailing_list_id'] = $path_args[2];

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Add'),
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
    $mailing_list_group = Group::load($form['#mailing_list_id']);

    foreach ($gids as $gid) {
      $gid = $gid['target_id'];

      if ($gid) {
        /** @var Group $group */
        $group = Group::load($gid);
        $membership = $group->getMembers([$group->bundle() . '-organisation']);

        foreach ($membership as $membershipgc) {
          /** @var GroupMembership $membershipgc */
          $org_uids[] = $membershipgc->getGroupContent()->getEntity()->id();

          foreach ($org_uids as $org_uid) {
            $org_user = User::load($org_uid);
            $mailing_list_group->addMember($org_user, ['group_roles' => [$group->bundle() . '-organisation']]);
          }

        }
      }
    }

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
    $config = [];
    /** @var ViewsBlock $plugin_block */
    $plugin_block = $block_manager->createInstance('views_block:mailing_list_organisations-block_1', $config);
    if ($plugin_block->access(\Drupal::currentUser())) {
      return $plugin_block->build();
    }
    return FALSE;
  }

}
