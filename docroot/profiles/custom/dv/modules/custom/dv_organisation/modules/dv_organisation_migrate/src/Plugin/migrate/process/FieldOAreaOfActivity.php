<?php

namespace Drupal\dv_organisation_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

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
        $query = \Drupal::entityQuery('taxonomy_term');

        $query->condition('vid', 'area_of_activity');
        $query->condition('field_area_of_activity_id', $id);
        $result = $query->execute();

        if($result) {
          $activites[] = reset($result);
        } else {
          $new_activity = Term::create([
            'vid' => 'area_of_activity',
            'name' => $name,
            'field_area_of_activity_id' => $id
          ]);

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
