<?php

namespace Drupal\import_organisations;

/**
 * Extract Telephones from string.
 *
 * Class ExtractTelephonesTrait
 */
trait ExtractTelephonesTrait {

  static function extract($value, $part_prefix) {

    if($value) {

      $parts = explode(';', $value);

      foreach ($parts as $part) {
        $part = strtolower(trim($part));

        if (substr($part, 0, 5) === $part_prefix) {
          $telephones = explode(',', $part);

          foreach ($telephones as $key => $telephone) {
            $telephones[$key] = str_replace($part_prefix, '', $telephone);
            $telephones[$key] = trim($telephones[$key]);

            $telephone_parts = explode(' ', $telephones[0]);

            $prefix = $telephone_parts[0] . ' ' . $telephone_parts[1];

            if (substr($telephones[$key], 0, strlen($prefix)) !== $prefix) {
              $telephones[$key] = $prefix . ' ' . $telephones[$key];
            }

          }
        }
      }

      if ($telephones) {
        return $telephones;
      }
    }

    return NULL;
  }

}