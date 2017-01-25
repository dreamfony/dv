<?php

namespace Drupal\geonames;

use Geonames\Geonames as ExternalGeonames;

/**
 * Main interface to the GeoNames API.
 *
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @link      http://www.geonames.org/export/web-services.html
 * @link      http://www.geonames.org/export/ws-overview.html
 *
 * @method array    children()                children(array $params)
 * @method array    cities()                  cities(array $params)
 * @method stdclass countryCode()             countryCode(array $params)
 * @method array    countryInfo()             countryInfo(array $params)
 * @method stdclass countrySubdivision()      countrySubdivision(array $params)
 * @method array    earthquakes()             earthquakes(array $params)
 * @method array    findNearby()              findNearby(array $params)
 * @method array    findNearbyPlaceName()     findNearbyPlaceName(array $params)
 * @method array    findNearbyPostalCodes()   findNearbyPostalCodes(array $params)
 * @method array    findNearbyStreets()       findNearbyStreets(array $params)
 * @method stdclass findNearByWeather()       findNearByWeather(array $params)
 * @method array    findNearbyWikipedia()     findNearbyWikipedia(array $params)
 * @method stdclass findNearestAddress()      findNearestAddress(array $params)
 * @method stdclass findNearestIntersection() findNearestIntersection(array $params)
 * @method stdclass get()                     get(array $params)
 * @method stdclass gtopo30()                 gtopo30(array $params)
 * @method array    hierarchy()               hierarchy(array $params)
 * @method stdclass neighbourhood()           neighbourhood(array $params)
 * @method array    neighbours()              neighbours(array $params)
 * @method array    postalCodeCountryInfo()   postalCodeCountryInfo(array $params)
 * @method array    postalCodeLookup()        postalCodeLookup(array $params)
 * @method array    postalCodeSearch()        postalCodeSearch(array $params)
 * @method array    search()                  search(array $params)
 * @method array    siblings()                siblings(array $params)
 * @method array    weather()                 weather(array $params)
 * @method stdclass weatherIcao()             weatherIcao(array $params)
 * @method stdclass srtm3()                   srtm3(array $params)
 * @method stdclass timezone()                timezone(array $params)
 * @method array    wikipediaBoundingBox()    wikipediaBoundingBox(array $params)
 * @method array    wikipediaSearch()         wikipediaSearch(array $params)
 */
class GeoNames {

  public function __construct( $username, $server, $token = null ) {
    $this->geonames = new ExternalGeonames($username, $token);
    $this->geonames->url = $server;
  }

  /**
   * Method interceptor that retrieves the corresponding endpoint and return
   * a json decoded object or throw a Exception.
   *
   * @param string $method   Method to call in External GeoNames library.
   * @param array  $params   Array of parameters to pass to the endpoint
   *
   * @return mixed stdclass|array The JSON decoded response or an array
   */
  public function __call( $method, $params ) {

    // handle params array
    if (isset($params[0])) {
      $params = is_array($params[0])
        ? $params[0]
        : [];
    }

    return $this->geonames->$method( $params );
  }

}