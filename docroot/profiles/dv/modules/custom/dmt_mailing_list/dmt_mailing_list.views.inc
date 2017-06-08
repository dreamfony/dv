<?php

/**
 * Implements hook_views_data_alter().
 */
function dmt_mailing_list_views_data_alter(array &$data) {
  $data['group_content']['group_content_ajax_delete_link'] = array(
    'title' => t('Group Content Ajax Delete Link'),
    'field' => array(
      'title' => t('Group Content Ajax Delete Link'),
      'help' => t('Group Content Ajax Delete Link.'),
      'id' => 'group_content_ajax_delete_link',
    ),
  );
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
