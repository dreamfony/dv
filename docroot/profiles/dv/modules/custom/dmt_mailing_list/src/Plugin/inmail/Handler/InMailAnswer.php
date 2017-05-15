<?php

namespace Drupal\dmt_mailing_list\Plugin\inmail\Handler;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\inmail\DefaultAnalyzerResult;
use Drupal\inmail\MIME\MimeMessageInterface;
use Drupal\inmail\Plugin\inmail\Handler\HandlerBase;
use Drupal\inmail\ProcessorResultInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\activity_creator\Entity\Activity;
use Drupal\inmail\MIME\MimeEncodings;
use Drupal\dmt_mailing_list\MailingListAnswer;

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
 *   id = "mailing_list_answer",
 *   label = @Translation("Mailing List Answer"),
 *   description = @Translation("Post comments via email.")
 * )
 */
class InMailAnswer extends HandlerBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\dmt_mailing_list\MailingListAnswer
   */
  protected $mailingListAnswers;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManager $entity_type_manager, MailingListAnswer $mailing_list_answers) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->mailingListAnswers = $mailing_list_answers;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('dmt_mailing_list.mailing_list_answer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function help() {
    return [
      '#type' => 'item',
      '#markup' => $this->t('Post mailing list answers via email.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function invoke(MimeMessageInterface $message, ProcessorResultInterface $processor_result) {

    try {
      /** @var DefaultAnalyzerResult $result */
      $result = $processor_result->getAnalyzerResult();

      $hash = $result->getContext('hash')->getContextValue();

      /** @var Activity $activity */
      $activity = Activity::getActivityEntityByHash($hash);

      if ($activity && $activity->bundle() == 'mailing_list_activity') {

        $user = $this->validateUser($result);

        $values = [
          'subject' => $message->getSubject(),
          'body' => $result->getBody(),
          'files' => $this->getAttachments($result),
          'user' => $user
        ];

        $this->mailingListAnswers->createAnswerFromActivity($activity, $values);

      } else {
        // ignore mail
        return;
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

}
