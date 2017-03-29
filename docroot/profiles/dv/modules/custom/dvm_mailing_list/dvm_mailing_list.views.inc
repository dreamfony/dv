<?php

/**
 * Implements hook_views_data_alter().
 */
function dvm_mailing_list_views_data_alter(array &$data) {
  $data['group_content']['group_content_ajax_delete_link'] = array(
    'title' => t('Group Content Ajax Delete Link'),
    'field' => array(
      'title' => t('Group Content Ajax Delete Link'),
      'help' => t('Group Content Ajax Delete Link.'),
      'id' => 'group_content_ajax_delete_link',
    ),
  );
}
