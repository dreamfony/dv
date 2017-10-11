<?php

namespace Drupal\dmt_positions;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Positions entities.
 *
 * @ingroup dmt_positions
 */
class PositionsListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Positions ID');
    $header['position'] = $this->t('Position');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\dmt_positions\Entity\Positions */

    $row['id'] = $entity->id();
    // Get referenced entity value, which is tax term name
    $row['position'] = $entity->field_positions_function->entity->name->value;
    $row['name'] = $this->l(
      $entity->id(),
      new Url(
        'entity.positions.edit_form', array(
          'positions' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
