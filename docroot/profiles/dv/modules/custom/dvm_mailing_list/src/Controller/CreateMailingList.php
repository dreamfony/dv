<?php

namespace Drupal\dvm_mailing_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;

/**
 * Class CreateMailingList.
 *
 * @package Drupal\dvm_mailing_list\Controller
 */
class CreateMailingList extends ControllerBase {

  /**
   * Creategroup.
   *
   * @return string
   *   Return Hello string.
   */
  public function createMailingList() {
    $group = Group::create([
      'label' => 'New Survey',
      'type' => 'mailing_list'
    ]);

    $group->save();

    /// @todo Check if user already has empty Survey

    return $this->redirect('entity.group.canonical', ['group' => $group->id()]);
  }

}
