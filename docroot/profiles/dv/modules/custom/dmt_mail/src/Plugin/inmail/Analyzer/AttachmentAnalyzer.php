<?php

namespace Drupal\dmt_mail\Plugin\inmail\Analyzer;

use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\inmail\DefaultAnalyzerResult;
use Drupal\inmail\MIME\MimeMessageInterface;
use Drupal\inmail\Plugin\inmail\Analyzer\AnalyzerBase;
use Drupal\inmail\ProcessorResultInterface;
use Drupal\inmail\MIME\MimeMultipartMessage;

/**
 * Attachments.
 *
 * @ingroup analyzer
 *
 * @Analyzer(
 *   id = "attachments",
 *   label = @Translation("Attachment Analyzer")
 * )
 */
class AttachmentAnalyzer extends AnalyzerBase {

  protected $attachments;

  /**
   * {@inheritdoc}
   */
  public function analyze(MimeMessageInterface $message, ProcessorResultInterface $processor_result) {

    /** @var DefaultAnalyzerResult $result */
    $result = $processor_result->getAnalyzerResult();

    /** @var \Drupal\Inmail\MIME\MimeMessageDecomposition $message_decomposition */
    $message_decomposition = \Drupal::service('inmail.message_decomposition');

    // Get the flattened list of entities for the processed message.
    $entities = $message_decomposition->getEntities($message);

    // Build multipart message parts in 'full' view mode.
    if ($message instanceof MimeMultipartMessage) {
      /** @var \Drupal\Inmail\MIME\MimeEntity $entity */
      foreach ($entities as $path => $entity) {
        switch ($entity->getType()) {
          case 'attachment':
            $this->attachments[$path] = $message_decomposition->buildAttachment($path, $entity, NULL);
            break;
        }
      }
    }

    // Add to context.
    $context_definition = new ContextDefinition('any', $this->t('Attachments'));
    $context = new Context($context_definition, $this->attachments);
    $result->setContext('attachments', $context);

  }

}
