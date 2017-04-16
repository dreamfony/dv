<?php

namespace Drupal\dvm_mailing_list\Plugin\views\field;

use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\Group;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dvm_mailing_list\MailingListAnswer;
use Drupal\Core\Form\FormStateInterface;

/**
 * Field handler to delete group content.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("group_content_mailing_list_stats")
 */
class GroupContentMailingListStats extends FieldPluginBase {

  /**
   * @var \Drupal\dvm_mailing_list\MailingListAnswer
   */
  protected $mailingListAnswers;

  /**
   * GroupContentMailingListStats constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\dvm_mailing_list\MailingListAnswer $mailing_list_answers
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailingListAnswer $mailing_list_answers) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mailingListAnswers = $mailing_list_answers;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dvm_mailing_list.mailing_list_answer')
    );
  }

  /**
   * Define the available options
   * @return array
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['status'] = array('default' => 'all');

    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    $statuses = ['all' => $this->t('All') ] + activity_creator_field_activity_status_allowed_values();

    $form['status'] = array(
      '#title' => $this->t('Activity Status'),
      '#description' => $this->t('Select a Activity Status you want the count for.'),
      '#type' => 'select',
      '#default_value' => $this->options['status'],
      '#options' => $statuses,
    );

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var GroupContent $group_content */
    $group_content = $values->_entity;

    /** @var Group $group */
    $group_id = $group_content->getGroup()->id();

    $user_id = $values->users_field_data_group_content_field_data_uid;

    // if total count then status is FALSE
    $status = $this->options['status'] == 'all' ? FALSE : $this->options['status'];

    $count = $this->mailingListAnswers->getAnswerCount($group_id, $user_id, $status);

    return $count;
  }
}
