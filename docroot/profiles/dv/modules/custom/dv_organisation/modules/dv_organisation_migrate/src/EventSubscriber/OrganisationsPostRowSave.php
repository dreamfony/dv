<?php

namespace Drupal\dv_organisation_migrate\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\group\Entity\Group;


/**
 * Class OrganisationsPostRowSave.
 *
 * @package Drupal\import_organisations
 */
class OrganisationsPostRowSave implements EventSubscriberInterface {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Create Organisation Person Relationship.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   */
  public function createOrganisationPersonRelationship(MigratePostRowSaveEvent $event) {

    /** @var  MigrationInterface $migration */
    $migration = $event->getMigration();

    if ($migration->id() === 'organisations') {

      /** @var Row $row */
      $row = $event->getRow();

      $destination_values = $event->getDestinationIdValues();

      $functionUpholderId = $row->getSourceProperty('function_upholder_id');

      $functions = $row->getSourceProperty('function');

      // if $functionUpholderId and $functions are array continue
      if (is_array($functionUpholderId) && is_array($functions)) {

        // combine function upholder ids with functions
        // upholder_id => function
        $functionUpholderIds = array_combine($functionUpholderId, $functions);

        foreach ($functionUpholderIds as $upholderId => $function) {

          // get person node
          $query = \Drupal::entityQuery('node');

          $query->condition('type', 'person');
          $query->condition('field_person_id', $upholderId);
          $person_id = $query->execute();

          $person = Node::load(reset($person_id));

          if ($person) {

            // get current positions ids from person node

            if ($person->field_person_positions->getValue()) {

              foreach ($person->field_person_positions->getValue() as $position_id) {
                $position_ids[] = $position_id['target_id'];
              }

              // check if there is a position with already attached position id to person node,
              // that has the same organisation as the one we are importing
              $query = \Drupal::entityQuery('positions');
              $query->condition('id', $position_ids, 'IN');
              /// @todo: write from to date condition
              $query->condition('field_positions_organisation', $destination_values[0]);
              $result = $query->execute();

            }

            // if there are none of already exiting positions create a new position
            // relationship
            if (!$result) {
              // create position entity
              $position = $this->entityTypeManager->getStorage('positions')
                ->create(
                  array(
                    /// @todo type may be missing here
                    'user_id' => 1
                  )
                );

              // assign organisation to position
              $position->set('field_positions_organisation', $destination_values[0]);

              // get function from taxonomy term
              $query = \Drupal::entityQuery('taxonomy_term');
              $query->condition('vid', 'functions');
              $query->condition('name', $function);
              $function_id = $query->execute();

              // set function
              $position->set('field_positions_function', reset($function_id));

              // save position
              $position->save();

              // append new position to person
              $person->field_person_positions->appendItem($position->id());

              // save person
              $person->save();
            }
          }
        }

        // since migrate can't import simple list of term ids we do it here
        $activity_ids = $row->getDestinationProperty('field_o_area_of_activity')[0];

        if (is_array($activity_ids)) {

          $organisation_node = Node::load($destination_values[0]);
          foreach ($activity_ids as $activity_id) {

            $activity_group = Group::load($activity_id);

            // add location node to created group
            $activity_group->addContent($organisation_node, 'group_node:' . $organisation_node->bundle());

          }
          $organisation_node->save();
        }

      }
      else {
        /// @todo: check if org. had people - phase 2 - org. update
      }
    }

  }

  /**
   * @inheritdoc
   */
  static function getSubscribedEvents() {
    return [
      MigrateEvents::POST_ROW_SAVE => [
        ['createOrganisationPersonRelationship']
      ],
    ];
  }


}
