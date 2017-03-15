<?php

namespace Drupal\hr_organisation_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\group\Entity\Group;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "field_o_area_of_activity"
 * )
 */
class FieldOAreaOfActivity extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if ($row->getSourceProperty('area_of_activity_id')) {

      foreach ($row->getSourceProperty('area_of_activity_id') as $id => $name) {
        $query = \Drupal::entityQuery('group');

        $query->condition('field_area_of_activity_id', $id);
        $result = $query->execute();

        if($result) {
          $activites[] = reset($result);
        } else {
          // create a group
          $new_activity = Group::create([
            'type' => 'area_of_activity',
            'uid' => 1,
            'label' => $name,
            'field_area_of_activity_id' => $id
          ]);

          // save new created group
          $new_activity->save();

          $activites[] = $new_activity->id();
        }
      }

      if ($activites) {
        return $activites;
      }
    }

    return NULL;
  }

}
