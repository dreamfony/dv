<?php

namespace Drupal\dmt_domain_config\Plugin\EntityReferenceSelection;

use Drupal\user\Entity\User;
use Drupal\domain\Plugin\EntityReferenceSelection\DomainSelection as DomainSelectionCore;

/**
 * Provides entity reference selections for the domain entity type.
 *
 * @EntityReferenceSelection(
 *   id = "dmt:domain",
 *   label = @Translation("Domain selection"),
 *   entity_types = {"domain"},
 *   group = "dmt",
 *   weight = 1
 * )
 */
class DomainSelection extends DomainSelectionCore {

  /**
   * {@inheritdoc}
   */
  public function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);

    // don't show default domain
    $query->condition('is_default', FALSE);

    // Can this user access inactive domains?
    if (!$this->currentUser->hasPermission('access inactive domains')) {
      $query->condition('status', 1);
    }
    // Filter domains by the user's assignments, which are controlled by other
    // modules. Those modules must know what type of entity they are dealing
    // with, so look up the entity type and bundle.
    $info = $query->getMetaData('entity_reference_selection_handler');

    if (!empty($info->configuration['entity'])) {
      $context['entity_type'] = $info->configuration['entity']->getEntityTypeId();
      $context['bundle'] = $info->configuration['entity']->bundle();
      $context['field_type'] = $this->field_type;

      // Load the current user.
      $account = User::load($this->currentUser->id());
      // Run the alter hook.
      $this->moduleHandler->alter('domain_references', $query, $account, $context);
    }

    return $query;
  }

}
