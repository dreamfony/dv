<?php

namespace Drupal\dmt_group\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\group\Entity\GroupType;

/**
 * Adds the item's URL to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "add_group_name",
 *   label = @Translation("DV Group Name"),
 *   description = @Translation("Adds the item's group name to the indexed data."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class AddGroupName extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = array();

    if (!$datasource) {

      //$group_types = GroupType::loadMultiple();


      //      TODO foreach group_type

      $group_type_id = 'test';

      $definition = array(
        'label' => $this->t('Group '. $group_type_id),
        'description' => $this->t('Group '. $group_type_id),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      );

      $properties['search_api_'. $group_type_id] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
//    TODO
    $url = $item->getDatasource()->getItemUrl($item->getOriginalObject());
    if ($url) {
      $fields = $this->getFieldsHelper()
        ->filterForPropertyPath($item->getFields(), NULL, 'search_api_group_type');
      foreach ($fields as $field) {
        if (!$field->getDatasourceId()) {
          $field->addValue($url->toString());
        }
      }
    }
  }

}
