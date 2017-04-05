<?php

namespace Drupal\dvm_mailing_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dvm_mailing_list\MailingList;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;


/**
 * Class CreateMailingList.
 *
 * @package Drupal\dvm_mailing_list\Controller
 */
class MailingListController extends ControllerBase {

  /** @var \Drupal\dvm_mailing_list\MailingList */
  protected $mailingList;

  /**
   * MailingListController constructor.
   * @param \Drupal\dvm_mailing_list\MailingList $mailing_list
   */
  public function __construct(MailingList $mailing_list) {
    $this->mailingList = $mailing_list;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dvm_mailing_list.mailing_list')
    );
  }

  /**
   * Create group.
   *
   * @return string
   *   Return Hello string.
   */
  public function createMailingList() {
    $mailing_list_id = $this->mailingList->createMailingList();
    return $this->redirect('entity.group.canonical', ['group' => $mailing_list_id]);
  }

  /**
   * Send for Approval.
   *
   * @param \Drupal\group\Entity\Group $group
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function sendForApproval(Group $group) {
    $this->mailingList->sendForApproval($group);
    return $this->redirect('entity.group.canonical', ['group' => $group->id()]);
  }

  /**
   * Approve sending.
   *
   * @param \Drupal\group\Entity\Group $group
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function approve(Group $group) {
    $this->mailingList->approve($group);
    return $this->redirect('entity.group.canonical', ['group' => $group->id()]);
  }

  /**
   * Edit Title.
   *
   * @param \Drupal\group\Entity\Group $group
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function editTitle(Group $group) {
    $form = \Drupal::formBuilder()->getForm('Drupal\dvm_mailing_list\Form\MailingListEditTitleForm', $group);

    $response = new AjaxResponse();
    $selector = '.block-mailing-list-title-block';
    $response->addCommand(new HtmlCommand($selector, $form));

    return $response;
  }

}
