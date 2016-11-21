<?php

namespace Drupal\dv_positions\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Positions entities.
 */
class PositionsViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['positions']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Positions'),
      'help' => $this->t('The Positions ID.'),
    );

    return $data;
  }

}
