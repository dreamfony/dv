<?php

namespace Drupal\dv_organisations_import\Plugin\migrate\source;

use Drupal\migrate_plus\Plugin\migrate\source\Url;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_plus\Plugin\migrate_plus\data_fetcher\Http;

/**
 * Retrieve data from a local path for migration.
 *
 * @MigrateSource(
 *   id = "file_proxy"
 * )
 */
class FileProxy extends Url {

  protected $dataFetcher;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    $this->dataFetcher = new Http($configuration, $plugin_id, $plugin_definition);

    if( !isset($configuration['preprocess']) ) {
      $configuration['preprocess'] = FALSE;
    }

    $urls = $this->fileProxy($configuration['preprocess']);

    $this->sourceUrls = $urls;

    $this->configuration['urls'] = $urls;

  }

  public function fileProxy($preProcess = FALSE) {

    $urls = [];

    foreach ($this->sourceUrls as $key => $url ) {

      $body = $this->dataFetcher->getResponseContent($url)->getContents();

      if($preProcess) {
        $body = $this->preProcessBody($body);
      }

      $file = file_save_data($body);
      $file->setTemporary();
      $file->save();

      $uri = $file->getFileUri();

      $new_url = file_create_url($uri);

      $urls[] = $new_url;

    }

    return $urls;

  }

  public function preProcessBody($body) {
    $body = str_replace('<NositeljFunkcije>', '<PERSON><NositeljFunkcije>', $body);
    $body = str_replace('</Funkcija>', '</Funkcija></PERSON>', $body);

    return $body;
  }

}