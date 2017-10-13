<?php

namespace Drupal\dmt_mailing_list\Plugin\views\field;

use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\Group;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\workflows\Entity\Workflow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dmt_mailing_list_activity\MailingListActivity;

/**
 * Field handler to delete group content.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("mailing_list_stats")
 */
class MailingListStats extends FieldPluginBase {

  /**
   * @var MailingListActivity
   */
  protected $mailingListActivity;

  /**
   * GroupContentMailingListStats constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param MailingListActivity $mailing_list_activity
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailingListActivity $mailing_list_activity) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mailingListActivity = $mailing_list_activity;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dmt_mailing_list_activity.mailing_list_activity')
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

    // get states from activity workflow
    $workflow = Workflow::load('mailing_list_activity_workflow');
    foreach ($workflow->getStates() as $state) {
      /** @var \Drupal\content_moderation\Entity\ContentModerationState $state */
      $statuses[$state->id()] = $state->label();
    }
    $statuses = ['all' => $this->t('All') ] + $statuses;

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
    $entity = $values->_entity;

    if($entity instanceof GroupContent) {
      $user_id = $values->users_field_data_group_content_field_data_uid;
      $group_id = $entity->getGroup()->id();
    } elseif($entity instanceof Group) {
      $user_id = FALSE;
      $group_id = $entity->id();
    }

    // if total count then status is FALSE
    $status = $this->options['status'] == 'all' ? FALSE : $this->options['status'];

    $count = $this->mailingListActivity->getAnswerCount($group_id, $user_id, $status);

    return $count;
  }
}
