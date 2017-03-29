<?php

namespace Drupal\dmt_organisation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dmt_organisation\Helper\Organisation;

/**
 * Class CreateOrganisation.
 *
 * @package Drupal\dmt_organisation\Controller
 */
class OrganisationController extends ControllerBase {

  protected $organisation;

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dmt_organisation.organisation')
    );
  }

  /**
   * OrganisationController constructor.
   * @param \Drupal\dmt_organisation\Helper\Organisation $organisation
   */
  public function __construct(Organisation $organisation) {
    $this->organisation = $organisation;
  }

  /**
   * Create Organisation.
   *
   * @return string
   */
  public function createOrganisation() {
    $user_id = $this->organisation->createOrganisation();

    // Redirect to user/$user_id/organisation_profile
    return $this->redirect('entity.profile.type.organisation_profile.user_profile_form', [
      'user' => $user_id,
      'profile_type' => 'organisation_profile'
    ]);
  }
}
