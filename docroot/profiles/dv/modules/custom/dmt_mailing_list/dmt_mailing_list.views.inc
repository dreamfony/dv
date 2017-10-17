<?php

/**
 * Implements hook_views_data_alter().
 */
function dmt_mailing_list_views_data_alter(array &$data) {
  $data['group_content']['group_content_mailing_list_stats'] = array(
    'title' => t('Mailing List Stats'),
    'field' => array(
      'title' => t('Mailing List Stats'),
      'help' => t('Mailing List Stats.'),
      'id' => 'mailing_list_stats',
    ),
  );
  $data['groups_field_data']['group_mailing_list_stats'] = array(
    'title' => t('Mailing List Stats'),
    'field' => array(
      'title' => t('Mailing List Stats'),
      'help' => t('Mailing List Stats.'),
      'id' => 'mailing_list_stats',
    ),
  );
  $data['activity']['reply_link'] = array(
    'title' => t('Reply Link'),
    'field' => array(
      'title' => t('Reply Link'),
      'help' => t('Reply link.'),
      'id' => 'activity_reply_link',
    ),
  );
}
