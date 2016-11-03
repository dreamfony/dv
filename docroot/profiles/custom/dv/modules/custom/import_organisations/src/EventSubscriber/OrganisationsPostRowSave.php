<?php

namespace Drupal\import_organisations\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


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

            // get current role ids from person node
            $roles_ids2 = $person->field_p_role->getValue();

            foreach ($roles_ids2 as $key => $val) {
              $roles_ids[] = $val['target_id'];
            }

            // check if there is a role with already attached role id to person node,
            // that has the same organisation as the one we are importing
            $query = \Drupal::entityQuery('role');
            $query->condition('id', $roles_ids, 'IN');
            $query->condition('type', 'role');
            /// @todo: write from to date condition
            $query->condition('field_r_organisation', $destination_values[0]);
            $result = $query->execute();

            // if there are none of already exiting roles create a new role
            // relationship
            if(!$result) {
              // create role entity
              $role = $this->entityTypeManager->getStorage('role')->create(
                array(
                  'type' => 'role',
                  'uid' => 1
                )
              );

              // assign organisation to role
              $role->set('field_r_organisation', $destination_values[0]);

              // get function from taxonomy term
              $query = \Drupal::entityQuery('taxonomy_term');
              $query->condition('vid', 'functions');
              $query->condition('name', $function);
              $function_id = $query->execute();

              // set function
              $role->set('field_r_function', reset($function_id));

              // save role
              $role->save();

              $role_id = $role->id();

              // append new role to person
              $person->field_p_role->appendItem($role_id);

              // save person
              $person->save();
            }
          }

        }

      } else {
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
