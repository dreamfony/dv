<?php

namespace Drupal\geo_area_migrate\EventSubscriber;

use Drupal\geo_area_migrate\Plugin\migrate\source\Geonames;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\geo_area_migrate\Plugin\migrate_plus\data_parser\XmlCrawler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\migrate_plus\Event\MigrateEvents;
use Drupal\migrate_plus\Event\MigratePrepareRowEvent;

/**
 * Class GeoAreaPrepareRow.
 *
 * @package Drupal\import_organisations
 */
class GeoAreaPrepareRow implements EventSubscriberInterface {


    public function addUrl(MigratePrepareRowEvent $event) {

      /** @var  MigrationInterface $migration */
      $migration = $event->getMigration();

      if($migration->id() === 'geoarea') {

        /** @var Row $row */
        $row = $event->getRow();

        /** @var Geonames $source */
        $source = $event->getSource();

        /** @var XmlCrawler $parser */
        $parser = $source->getDataParserPlugin();

        // if item has children
        if ($row->getSourceProperty('num_of_children') > 0 ) {
          // add new url to the list of urls that need to be parsed
          $parser->urls[] = $source->constructURL( $row->getSourceProperty('geoname_id') );
        }
      }

    }


  public function setFieldGeoAreaParent(MigratePrepareRowEvent $event) {

    /** @var  MigrationInterface $migration */
    $migration = $event->getMigration();

    if($migration->id() === 'geoarea') {

      /** @var Row $row */
      $row = $event->getRow();

      /** @var Geonames $source */
      $source = $event->getSource();

      /** @var XmlCrawler $parser */
      $parser = $source->getDataParserPlugin();

      // get geonameId from current url
      $currentUrlGeonameId = $parser->getCurrentUrlGeonameId();

      // set source property to geonameId in url
      $row->setSourceProperty('field_geo_area_parent', $currentUrlGeonameId);
    }

  }

    /**
     * @inheritdoc
     */
    static function getSubscribedEvents() {
        return [
            MigrateEvents::PREPARE_ROW => [
                ['addUrl'],
                ['setFieldGeoAreaParent']
            ],
        ];
    }


}
