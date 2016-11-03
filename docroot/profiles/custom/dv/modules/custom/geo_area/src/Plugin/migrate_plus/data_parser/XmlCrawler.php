<?php

namespace Drupal\geo_area\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Xml;

/**
 * Obtain XML data for migration.
 *
 * @DataParser(
 *   id = "xml_crawler",
 *   title = @Translation("XML Crawler")
 * )
 */
class XmlCrawler extends Xml  {

  /**
   * List of source urls.
   *
   * @var string[]
   */
  public $urls;

  /**
   * Index of the currently-open url.
   *
   * @var int
   */
  public $activeUrl;

  /**
   * Query paramters
   *
   * @var string[]
   */
  public $queryParams;

  /**
   * Get geonameId form active url
   */
  function getCurrentUrlGeonameId() {
    $currentURL = $this->urls[$this->activeUrl];
    $parsedURL = parse_url($currentURL);
    $query = $parsedURL['query'];
    parse_str($query, $query_params);
    $this->queryParams[] = $query_params['geonameId'];
    return $query_params['geonameId'];
  }

}
