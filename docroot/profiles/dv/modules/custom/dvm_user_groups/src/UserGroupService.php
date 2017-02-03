<?php

namespace Drupal\dvm_user_groups;

use Drupal\group\Entity\Group;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\group\Entity\GroupContent;

/**
 * Class UserGroupService
 *
 * @package Drupal\private_group
 */
class UserGroupService {

  /** @var  string */
  protected $groupType;

  /** @var \Drupal\Core\Entity\ContentEntityInterface */
  protected $entity;

  /** @var \Drupal\group\Entity\GroupInterface */
  protected $group;

  /** @var int */
  protected $groupId;

  /** @var \Drupal\user\UserInterface $owner */
  protected $owner;

  /**
   * Group User Issue.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param $action
   */
  public function groupUserIssue(ContentEntityInterface $entity, $action) {
    $this->entity = $entity;

    if($action == 'insert') {

      if ($entity->get('field_i_private')->value == 1) {
        $this->groupType = 'private_group';
      } else {
        $this->groupType = 'public_group';
      }

      $this->add($entity);

    } elseif($action == 'update') {

      $groupChanged = 0;

      // check if field_i_private has changed form 0 to 1
      if ($entity->get('field_i_private')->value == 1 && $entity->original->get('field_i_private')->value == 0) {
        $this->groupType = 'private_group';
        $groupChanged = 1;
      }
      elseif ($entity->get('field_i_private')->value == 0 && $entity->original->get('field_i_private')->value == 1) {
        $this->groupType = 'public_group';
        $groupChanged = 1;
      }

      // if field_i_private changed
      if($groupChanged == 1) {
        // remove node form the previous group
        $this->remove($entity);
        // add node to new group
        $this->add($entity);
      }

    }
  }

  /**
   * Creates a group. Adds entity to a group.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  protected function add(ContentEntityInterface $entity) {

    $this->entity = $entity;
    $this->owner = $entity->getOwner();

    if($groupId = $this->getUserGroupId()) {
      $this->loadGroup($groupId);
    } else {
      $this->createGroup();
    }

    $this->addContent();

  }

  /**
   * Remove content form the private group.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  protected function remove(ContentEntityInterface $entity) {

    $groupContents = GroupContent::loadByEntity($entity);

    foreach ($groupContents as $value) {
      /** @var GroupContent $groupContent */
      $groupContent = $value;
      /** @var Group $group */
      $groupType = $groupContent->getGroup()->getGroupType()->id();

      // check if group is one of user groups
      if ($groupType === 'private_group' || $groupType === 'public_group') {
        // if group type is not current group type
        if($groupType != $this->groupType) {
          $groupContent->delete();
        }
      }

    }

  }

  /**
   * Load group.
   *
   * @param $groupId
   */
  protected function loadGroup($groupId) {
    $this->group = Group::load($groupId);
  }

  /**
   * Create a group.
   */
  protected function createGroup() {
    // create a group
    $this->group = Group::create([
      'type' => $this->groupType,
      'uid' => $this->owner->id(),
      'label' => $this->owner->getAccountName()
    ]);

    // save new created group
    $this->group->save();
  }

  /**
   * Add content to group.
   */
  protected function addContent() {
    $this->group->addContent($this->entity, 'group_node:' . $this->entity->bundle());
  }

  /**
   * Find if user has a group of type.
   */
  protected function getUserGroupId() {
    $result = views_get_view_result('getusergroup', 'rest_export_1', $this->owner->id(), $this->groupType);
    return $result[0]->id;
  }

}
