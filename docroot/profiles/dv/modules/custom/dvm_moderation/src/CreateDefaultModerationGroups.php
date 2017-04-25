<?php
namespace Drupal\dvm_moderation;

use Drupal\user\Entity\User;
use Drupal\group\Entity\Group;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\Yaml\Yaml;

/**
 * Implements Demo content for Groups.
 */
class CreateDefaultModerationGroups {

  /** @var mixed */
  protected $groups;

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\entity\EntityStorageInterface
   */
  protected $groupStorage;


  /**
   * CreateDefaultModerationGroups constructor.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_manager
   */
  public function __construct(EntityTypeManager $entity_manager) {
    $this->groupStorage = $entity_manager->getStorage('group');
    $file = 'ModerationGroups.yml';
    $this->groups = Yaml::parse(file_get_contents($this->getPath($file)));
  }

  /**
   * Function to create content.
   */
  public function createContent() {

    // Loop through the content and try to create new entries.
    foreach ($this->groups as $uuid => $group_data) {

      /// skip if item is not enabled
      if($group_data['status'] === false) {
        continue;
      }

      $user = user_load_by_name($group_data['user']);

      $this->createGroup($uuid, $group_data, $user);

    }
  }

  public function createGroup($uuid, $group_data, User $user) {
    // Let's create some groups.
    $group_object = Group::create([
      'uuid' => $uuid,
      'type' => $group_data['group_type'],
      'label' => $group_data['title'],
      'uid' => $user->id(),
    ]);
    $group_object->save();
  }

  /**
   * Returns the path for the given file.
   *
   * @param string $file
   *   The filename string.
   * @param string $module
   *   The module where the Yaml file is placed.
   *
   * @return string
   *   String with the full pathname including the file.
   */
  public function getPath($file, $module = 'dvm_moderation') {
    return drupal_get_path('module', $module) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . $file;
  }

}
