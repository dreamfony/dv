<?php

namespace Drupal\dmt_organisation_group;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;
use Drupal\profile\Entity\Profile;
use Drupal\user\Entity\User;

/**
 * Class AddGroupToSubgroup
 *
 * @package Drupal\dmt_organisation_group
 */
class AddGroupToSubgroup {

  /** @var  string */
  protected $groupType;

  /** @var Profile */
  protected $profile;

  /** @var \Drupal\group\Entity\GroupInterface */
  protected $group;

  /** @var \Drupal\group\Entity\GroupInterface */
  protected $parentGroup;

  /** @var  string */
  protected $fieldMachineName;

  /**
   * Creates a group. Adds entity to a group.
   *
   * @param \Drupal\profile\Entity\Profile $profile
   * @param $groupType
   * @param $fieldMachineName
   */
  public function add(Profile $profile, $groupType, $fieldMachineName) {

    $this->profile = $profile;
    $this->groupType = $groupType;
    $this->fieldMachineName = $fieldMachineName;

    // create a group
    $this->group = Group::create([
      'type' => $this->groupType,
      'uid' => 1,
      'label' => $this->profile->get('field_org_title')
    ]);

    // save new created group
    $this->group->save();

    // save group reference to profile
    // TODO Why is it neccessary to load again profile?
    $profile = Profile::load($this->profile->id());
    $profile->field_org_related_group->target_id = $this->group->id();
    $profile->save();

    $profile_owner = $this->profile->getOwner();

    // add profile owner to the group
    $this->group->addMember($profile_owner, ['group_roles' => [$this->group->bundle().'-organisation']]);

    // get parent entity id
    $parentEntityId = $this->profile->get($fieldMachineName)->target_id;

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
    if ( $user = User::load($parentEntityId) ) {
      if ($groupContents = GroupContent::loadByEntity($user)) {
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
