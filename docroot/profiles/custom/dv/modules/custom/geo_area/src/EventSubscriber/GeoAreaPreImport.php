<?php

namespace Drupal\geo_area\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\node\Entity\Node;

/**
 * Class GeoAreaPreImport
 * @package Drupal\geo_area\EventSubscriber
 */
class GeoAreaPreImport implements EventSubscriberInterface {


  public function addSourceGeoArea(MigrateImportEvent $event) {

    /** @var  MigrationInterface $migration */
    $migration = $event->getMigration();

    if($migration->id() === 'geoarea') {

      $source = $migration->getSourceConfiguration();

      $query = \Drupal::entityQuery('node');

      $query->condition('type', 'geo_area');
      $query->condition('field_geo_area_geonames_id', $source['geonameid'] );
      $result = $query->execute();

      if (!$result) {
        // create starting geo area
        $geo_area = Node::create([
          'type'        => 'geo_area',
          'title'       => $source['start_name'],
          'field_geo_area_geonames_id' => $source['geonameid'],
        ]);

        $geo_area->save();

      }

    }

  }

  /**
   * @inheritdoc
   */
  static function getSubscribedEvents() {
    return [
      MigrateEvents::PRE_IMPORT => [
        ['addSourceGeoArea']
      ],
    ];
  }


}
