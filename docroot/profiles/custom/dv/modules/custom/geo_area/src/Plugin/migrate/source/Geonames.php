<?php

namespace Drupal\geo_area\Plugin\migrate\source;

use Drupal\migrate\Plugin\MigrateSourceInterface;
use Drupal\migrate_plus\Plugin\migrate\source\SourcePluginExtension;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Retrieve data from a local path for migration.
 *
 * @MigrateSource(
 *   id = "geo_names"
 * )
 */
class Geonames extends SourcePluginExtension implements MigrateSourceInterface {

  protected $geoname_url = 'http://api.geonames.org/children?geonameId=NNN&username=mailinator&style=FULL';

  /**
   * The source URLs to retrieve.
   *
   * @var array
   */
  protected $sourceUrls = [];

  /**
   * The data parser plugin.
   *
   * @var \Drupal\migrate_plus\DataParserPluginInterface
   */
  protected $dataParserPlugin;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    $urls = [
      'http://api.geonames.org/children?geonameId=' . $configuration['geonameid'] . '&username=mailinator&style=FULL'
    ];

    $this->sourceUrls = $urls;

    $this->configuration['urls'] = $this->sourceUrls;
  }

  /**
   * Return a string representing the source URLs.
   *
   * @return string
   *   Comma-separated list of URLs being imported.
   */
  public function __toString() {
    // This could cause a problem when using a lot of urls, may need to hash.
    $urls = implode(', ', $this->sourceUrls);
    return $urls;
  }

  /**
   * Returns the initialized data parser plugin.
   *
   * @return \Drupal\migrate_plus\DataParserPluginInterface
   *   The data parser plugin.
   */
  public function getDataParserPlugin() {
    if (!isset($this->dataParserPlugin)) {
      $this->dataParserPlugin = \Drupal::service('plugin.manager.migrate_plus.data_parser')->createInstance($this->configuration['data_parser_plugin'], $this->configuration);
    }
    return $this->dataParserPlugin;
  }

  /**
   * Creates and returns a filtered Iterator over the documents.
   *
   * @return \Iterator
   *   An iterator over the documents providing source rows that match the
   *   configured item_selector.
   */
  protected function initializeIterator() {
    return $this->getDataParserPlugin();
  }

  public function constructURL($id) {
    return str_replace('NNN', $id, $this->geoname_url);
  }

}