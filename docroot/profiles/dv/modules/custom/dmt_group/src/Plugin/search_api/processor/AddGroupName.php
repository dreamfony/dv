<?php

namespace Drupal\dmt_group\Plugin\search_api\processor;

use Drupal\group\Entity\Group;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\group\Entity\GroupType;
use Drupal\group\Entity\GroupContent;

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

      $group_types = GroupType::loadMultiple();

      foreach ($group_types as $group_type_id => $group_type) {
        $definition = array(
          'label' => $this->t('Group ' . $group_type->label()),
          'description' => $this->t('Group ' . $group_type_id),
          'type' => 'string',
          'processor_id' => $this->getPluginId(),
          'group_type' =>$group_type_id
        );

        $properties['search_api_' . $group_type_id] = new ProcessorProperty($definition);
      }
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();

    $groupContent = \Drupal::entityTypeManager()
      ->getStorage('group_content')
      ->loadByProperties([
        'type' => ['group_content_type_2d36600d04881'],
        'entity_id' => $entity->id(),
      ]);

    if ($groupContent) {
      foreach ($groupContent as $gc) {
        /** @var GroupContent $gc */
        $group = $gc->getGroup();
        // Potentially there are more than one.
        // Set the group id.
        $group_bundle = $group->bundle();
        $group_label = $group->label();

        if ($group_bundle) {
          $fields = $this->getFieldsHelper()
            ->filterForPropertyPath($item->getFields(), NULL, 'search_api_' . $group_bundle);
          foreach ($fields as $field) {
            if (!$field->getDatasourceId()) {
              $field->addValue($group_label);
            }
          }
        }
      }
    }
  }
}
