<?php

namespace Drupal\dv_organisation_migrate\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Xml;

/**
 * Obtain XML data for migration.
 *
 * @DataParser(
 *   id = "xml2",
 *   title = @Translation("XML2")
 * )
 */
class Xml2 extends Xml {

  /**
   * Implementation of Iterator::next().
   */
  public function next() {
    $this->currentItem = $this->currentId = NULL;
    if (is_null($this->activeUrl)) {
      if (!$this->nextSource()) {
        // No data to import.
        return;
      }
    }
    // At this point, we have a valid open source url, try to fetch a row from
    // it.
    $this->fetchNextRow();
    // If there was no valid row there, try the next url (if any).
    if (is_null($this->currentItem)) {
      $this->fetchNextRow();
    }
    if ($this->valid()) {
      foreach ($this->configuration['ids'] as $id_field_name => $id_info) {
        $this->currentId[$id_field_name] = $this->currentItem[$id_field_name];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function fetchNextRow() {
    $target_element = NULL;

    // Loop over each node in the XML file, looking for elements at a path
    // matching the input query string (represented in $this->elementsToMatch).
    while ($this->reader->read()) {
      if ($this->reader->nodeType == \XMLReader::ELEMENT) {
        if ($this->prefixedName) {
          $this->currentPath[$this->reader->depth] = $this->reader->name;
          if (array_key_exists($this->reader->name, $this->parentElementsOfInterest)) {
            $this->parentXpathCache[$this->reader->depth][$this->reader->name][] = $this->getSimpleXml();
          }
        }
        else {
          $this->currentPath[$this->reader->depth] = $this->reader->localName;
          if (array_key_exists($this->reader->localName, $this->parentElementsOfInterest)) {
            $this->parentXpathCache[$this->reader->depth][$this->reader->name][] = $this->getSimpleXml();
          }
        }
        if ($this->currentPath == $this->elementsToMatch) {
          // We're positioned to the right element path - build the SimpleXML
          // object to enable proper xpath predicate evaluation.
          $target_element = $this->getSimpleXml();
          if ($target_element !== FALSE) {
            if (empty($this->xpathPredicate) || $this->predicateMatches($target_element)) {
              break;
            }
          }
        }
      }
      elseif ($this->reader->nodeType == \XMLReader::END_ELEMENT) {
        // Remove this element and any deeper ones from the current path.
        foreach ($this->currentPath as $depth => $name) {
          if ($depth >= $this->reader->depth) {
            unset($this->currentPath[$depth]);
          }
        }

        foreach ($this->parentXpathCache as $depth => $elements) {
          if ($depth > $this->reader->depth) {
            unset($this->parentXpathCache[$depth]);
          }
        }
      }
    }

    // If we've found the desired element, populate the currentItem and
    // currentId with its data.
    if ($target_element !== FALSE && !is_null($target_element)) {
      foreach ($this->fieldSelectors() as $field_name => $xpath) {
        foreach ($target_element->xpath($xpath) as $value) {
          $this->currentItem[$field_name][] = (string) $value;
        }
      }
      // Reduce single-value results to scalars.
      if($this->currentItem) {
        foreach ($this->currentItem as $field_name => $values) {
          if (count($values) == 1) {
            $this->currentItem[$field_name] = reset($values);
          }
        }
      }
    }

  }
}