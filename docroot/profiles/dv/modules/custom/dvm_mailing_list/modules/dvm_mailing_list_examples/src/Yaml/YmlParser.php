<?php
namespace Drupal\dvm_mailing_list_examples\Yaml;

use Symfony\Component\Yaml\Yaml;

class YmlParser extends Yaml {
  /**
   * Returns the path for the given file.
   *
   * @param string $file
   *   The filename string.
   * @param string $module
   *   The module where the Yaml file is placed.
   *
   * @return string
   *   String with the full pathname including the file.
   */
  public function getPath($file, $module = 'dvm_mailing_list_examples') {
    // @todo Fix this for other file paths?!.
    return drupal_get_path('module', $module) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . $file;
  }
  /**
   * {@inheritdoc}
   */
  public function parseFile($file, $module = 'dvm_mailing_list_examples') {
    return $this->parse(file_get_contents($this->getPath($file, $module)));
  }
}
