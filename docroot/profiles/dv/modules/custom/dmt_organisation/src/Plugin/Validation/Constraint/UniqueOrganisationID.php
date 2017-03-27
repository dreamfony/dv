<?php


namespace Drupal\dmt_organisation\Plugin\Validation\Constraint;

use Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldConstraint;

/**
 * Checks if a field is unique.
 *
 * @Constraint(
 *   id = "UniqueOrganisationID",
 *   label = @Translation("DmtOrganisation unique Organisation ID", context = "Validation"),
 * )
 */
class UniqueOrganisationID extends UniqueFieldConstraint {

//  TODO sruši se stranica kad ne prođe validaciju
  public $message = 'The code %value is already taken.';

//$organisation_id_used = Drupal::service('dmt_organisation.organisation')
//->checkOrganisationIdIsUsed();

}