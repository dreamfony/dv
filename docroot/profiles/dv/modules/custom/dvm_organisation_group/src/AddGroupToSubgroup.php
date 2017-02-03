<?php

namespace Drupal\dvm_organisation_group;

use Drupal\group\Entity\Group;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\node\Entity\Node;
use Drupal\group\Entity\GroupContent;

/**
 * Class AddGroupToSubgroup
 *
 * @package Drupal\dvm_organisation_group
 */
class AddGroupToSubgroup {

  /** @var  string */
  protected $groupType;

  /** @var Node */
  protected $entity;

  /** @var \Drupal\group\Entity\GroupInterface */
  protected $group;

  /** @var \Drupal\group\Entity\GroupInterface */
  protected $parentGroup;

  /** @var  string */
  protected $fieldMachineName;

  /**
   * Creates a group. Adds entity to a group.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param $groupType
   * @param $fieldMachineName
   */
  public function add(ContentEntityInterface $entity, $groupType, $fieldMachineName) {

    $this->entity = $entity;
    $this->groupType = $groupType;
    $this->fieldMachineName = $fieldMachineName;

    // create a group
    $this->group = Group::create([
      'type' => $this->groupType,
      'uid' => 1,
      'label' => $entity->label()
    ]);

    // save new created group
    $this->group->save();

    // add node to created group
    $this->group->addContent($entity, 'group_node:' . $entity->bundle());

    // add node owner to group
    $this->group->addMember($this->entity->getOwner(), ['group_roles' => ['organisation-organisation']]);

    // get parent entity id
    $parentEntityId = $this->entity->get($fieldMachineName)->target_id;

    // if parent group exits
    if (!empty($parentEntityId) && $parentGroupId = $this->getParentGroupId($parentEntityId)) {
      // load parent group
      $this->parentGroup = Group::load($parentGroupId);
      // add group to parent group
      $this->addGroupToParentGroup();
    }

  }

  /**
   * Add Location node to parent group.
   */
  protected function addGroupToParentGroup() {
    // add to parent group
    $this->parentGroup->addContent($this->group, 'subgroup:' . $this->groupType);
  }

  /**
   * Find parent group.
   *
   * @param $parentEntityId
   * @return mixed
   */
  protected function getParentGroupId($parentEntityId) {
    if ( $node = Node::load($parentEntityId) ) {
      if ($groupContents = GroupContent::loadByEntity($node)) {
        // Potentially there are more than one.
        foreach ($groupContents as $groupContent) {
          /** @var GroupContent $groupContent */
          // Set the group id.
          /** @var Group $group */
          $group = $groupContent->getGroup();
          $group_type = $group->bundle();
          if($group_type === 'organisation') {
            return $group->id();
          }
        }

      }
    }
  }

}
