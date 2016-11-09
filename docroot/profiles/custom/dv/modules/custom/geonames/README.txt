Before you start using module please go to:
/admin/config/geonames
and set your Username, Server and Token

Usage example:
==============

Simple get request by geonameId:
$test2 = \Drupal::service('geonames.geonames')->get( ['geonameId' => $geonames_id] );

List of all methods can be found in src/GeoNames.php

