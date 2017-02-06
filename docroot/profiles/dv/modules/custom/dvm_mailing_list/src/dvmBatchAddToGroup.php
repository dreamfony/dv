<?php

namespace Drupal\dvm_mailing_list;

use Drupal\node\Entity\Node;


class dvmBatchAddToGroup {

  public static function deleteNodeExample($group_id, $organizations, &$context){
    $message = 'Deleting Node...';
    $results = array();
    foreach ($nids as $nid) {
      $node = Node::load($nid);
      $results[] = $node->delete();
    }
    $context['message'] = $message;
    $context['results'] = $results;
  }

  function deleteNodeExampleFinishedCallback($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One post processed.', '@count posts processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    drupal_set_message($message);
  }
}