<?php

namespace Drupal\dvm_mailing_list_examples\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\dvm_mailing_list_examples\MailingListExamples;
use Drupal\group\Entity\Group;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dvm_mailing_list\MailingList;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;


/**
 * Class MailingListController
 *
 * @package Drupal\dvm_mailing_list_examples\Controller
 */
class MailingListController extends ControllerBase {

  /** @var \Drupal\dvm_mailing_list\MailingList */
  protected $mailingListExamples;

  /**
   * MailingListController constructor.
   *
   * @param \Drupal\dvm_mailing_list_examples\MailingListExamples $mailing_list
   */
  public function __construct(MailingListExamples $mailing_list_examples) {
    $this->mailingListExamples = $mailing_list_examples;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dvm_mailing_list_examples.mailing_list_examples')
    );
  }

  /**
   * Create group.
   *
   * @return string
   *   Return Hello string.
   */
  public function createMailingList() {
    $mailing_list_id = $this->mailingListExamples->createContent();
    return $this->redirect('entity.group.canonical', ['group' => $mailing_list_id]);
  }

}
