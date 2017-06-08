<?php

/**
 * Trigger batch on approved state.
 *
 * @param $form
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 */
function _dmt_mailing_list_group_form_submit($form, \Drupal\Core\Form\FormStateInterface $form_state) {

  $group = $form_state->getFormObject()->getEntity();
  $mod_state = $group->get('moderation_state')->getValue()[0]['value'];

  if ($mod_state === 'published') {

    /** @var \Drupal\Core\Url $redirect */
    $redirect = $form_state->getRedirect();
    $group_id = $redirect->getRouteParameters()['group'];

// getMembership gids
    $gids = $form_state->getValue('field_ml_to');

//  getIssue nids
    $group = Group::load($group_id);
    $nids = $group->get('field_ml_contents')->getValue();

    $batch = array(
      'title' => t('Set up Survey...'),
      'operations' => array(
        array(
          '\Drupal\dmt_mailing_list\BatchMailingList::cleanGroup',
          array($group_id)
        ),
        array(
          '\Drupal\dmt_mailing_list\BatchMailingList::addMembers',
          array($gids, $group_id)
        ),
        array(
          '\Drupal\dmt_mailing_list\BatchMailingList::addContents',
          array($nids, $group_id)
        ),
      ),
      'init_message' => t('Example Batch is starting.'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message' => t('Example Batch has encountered an error.'),
      'finished' => '\Drupal\dmt_mailing_list\BatchMailingList::deleteNodeExampleFinishedCallback',
    );
    batch_set($batch);
  }

}
