<?php

namespace Drupal\dmt_mail\Plugin\inmail\Handler;

use Drupal\comment\CommentInterface;
use Drupal\comment\Entity\Comment;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\inmail\DefaultAnalyzerResult;
use Drupal\inmail\MIME\MimeMessageInterface;
use Drupal\inmail\Plugin\inmail\Handler\HandlerBase;
use Drupal\inmail\ProcessorResultInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\activity_creator\Entity\Activity;
use Drupal\inmail\MIME\MimeEncodings;

/**
 * Message handler that supports posting comments via email.
 *
 * This handler creates a new comment entity on the configured entity type if
 * user (anonymous or authenticated user) has required permissions to create
 * one.
 * It is triggered in case the mail subject begins with "[comment][#entity_ID]"
 * pattern.
 *
 * @Handler(
 *   id = "dmt_comment",
 *   label = @Translation("DMT Mail Comment"),
 *   description = @Translation("Post comments via email.")
 * )
 */
class DmtMailComment extends HandlerBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Entity
   *
   * @var EntityInterface
   */
  protected $entity;

  /**
   * Comment type
   *
   * @var string
   */
  protected $commentType;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function help() {
    return [
      '#type' => 'item',
      '#markup' => $this->t('Post comments via email.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function invoke(MimeMessageInterface $message, ProcessorResultInterface $processor_result) {
    // make sure this plugin does NOT run at all
    // we can probably just delete the whole plugin
    return;

    try {
      /** @var DefaultAnalyzerResult $result */
      $result = $processor_result->getAnalyzerResult();

      $hash = $result->getContext('hash')->getContextValue();

      /** @var Activity $activity */
      $activity = Activity::getActivityEntityByHash($hash);

      $activity_type = $activity->bundle();


      if ($activity) {
        $related_object = $activity->get('field_activity_entity')->getValue();

        if (!empty($related_object)) {
          $ref_entity_type = $related_object['0']['target_type'];
          $ref_entity_id = $related_object['0']['target_id'];
          $this->entity = $this->entityTypeManager
            ->getStorage($ref_entity_type)
            ->load($ref_entity_id);
          $this->commentType = $this->entity->get('field_content_comment_type')
            ->getString();

        }
        else {
          // ignore mail
          return;
        }

        // Create a comment.
        $comment = $this->createComment($message, $result);

        // set activity status to answered
        $activity->setModerationState('answered');

        // set comment reply
        $activity->field_activity_reply[] = $comment->id();

        // save activity
        $activity->save();

        /* we do not need this
        $processor_result->log('CommentHandler', '@comment has been created by @user.', [
          '@comment' => $comment->label(),
          '@user' => $comment->getAuthorName()
        ]);
        */
      }
      else {
        \Drupal::logger('dmt_mail')
          ->notice('Email with invalid hash entered the system: @hash',
            array(
              '@hash' => $hash,
            ));
      }
    } catch (\Exception $e) {
      // Log error in case verification, authentication or authorization fails.
      $processor_result->log('CommentHandler', $e->getMessage());
    }
  }

  /**
   * @param \Drupal\inmail\DefaultAnalyzerResult $result
   * @return array
   */
  protected function getAttachments(DefaultAnalyzerResult $result) {
    $managed_files = [];

    $attachments = $result->getContext('attachments')->getContextValue();

    foreach ($attachments as $attachment) {
      $path = 'public://attachments/' . date('Y-m', time()) . '/' . date('d', time()) . '/';
      if (file_prepare_directory($path, FILE_CREATE_DIRECTORY)) {
        $decoded_content = MimeEncodings::decode($attachment['content'], $attachment['encoding']);
        $file = file_save_data($decoded_content, $path . $attachment['filename']);
        $managed_files[] = ['target_id' => $file->id()];
      }
    }

    return $managed_files;
  }

  /**
   * Creates a new comment from given mail message.
   *
   * @param \Drupal\inmail\MIME\MimeMessageInterface $message
   *   The mail message.
   * @param \Drupal\inmail\DefaultAnalyzerResult $result
   *   The analyzer result.
   *
   * @return \Drupal\comment\Entity\Comment
   *   The created comment.
   *
   * @throws \Exception
   *   Throws an exception in case user is not authorized to create a comment.
   */
  protected function createComment(MimeMessageInterface $message, DefaultAnalyzerResult $result) {
    // Validate whether user is allowed to post comments.
    $user = $this->validateUser($result);

    // Create a comment entity.
    $comment = Comment::create([
      'entity_type' => $this->configuration['entity_type'],
      'entity_id' => $this->entity->id(),
      'uid' => $user->id(),
      'subject' => $message->getSubject(),
      'comment_body' => [
        'value' => $result->getBody(),
        'format' => 'basic_html',
      ],
      'field_name' => 'field_content_answers',
      'comment_type' => $this->commentType,
      'status' => CommentInterface::PUBLISHED,
    ]);

    // handle attachments
    $files = $this->getAttachments($result);

    if(!empty($files)) {
      $comment->set('field_c_attachments', $files);
    }

    $comment->save();

    return $comment;
  }

  /**
   * Checks if the user is authenticated and authorized to post comments.
   *
   * @param \Drupal\inmail\DefaultAnalyzerResult $result
   *   The analyzer result.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The identified account.
   *
   * @throws \Exception
   *   Throws an exception in case user is not validated.
   */
  protected function validateUser(DefaultAnalyzerResult $result) {
    // Do not allow unverified PGP-signed messages.
    if ($result->hasContext('verified') && !$result->getContext('verified')
        ->getContextValue()
    ) {
      throw new \Exception('Failed to process the message. PGP-signed message is not verified.');
    }

    // Get the current user.
    $account = \Drupal::currentUser()->getAccount();

    // Authorize a user.
    $access = $this->entityTypeManager->getAccessControlHandler('comment')
      ->createAccess('comment', $account, [], TRUE);
    if (!$access->isAllowed()) {
      throw new \Exception('Failed to process the message. User is not authorized to post comments.');
    }

    return $account;
  }

  /**
   * Returns an array of entity types.
   *
   * @return array
   *   An array of entity types.
   */
  protected function getEntityTypes() {
    // Get a mapping of entity types (bundles) with comment fields.
    $comment_entity_types = \Drupal::entityManager()
      ->getFieldMapByFieldType('comment');
    $entity_types = [];

    foreach ($comment_entity_types as $entity_type => $bundles) {
      $entity_types[$entity_type] = $this->entityTypeManager->getDefinition($entity_type)
        ->getLabel();
    }

    return $entity_types;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'entity_type' => 'node',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['entity_type'] = [
      '#title' => $this->t('Entity type'),
      '#type' => 'select',
      '#options' => $this->getEntityTypes(),
      '#default_value' => $this->configuration['entity_type'],
      '#description' => $this->t('Select a referenced entity type.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['entity_type'] = $form_state->getValue('entity_type');
  }

}
