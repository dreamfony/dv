<?php

namespace Drupal\dv_mailhandler\Plugin\inmail\Handler;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\inmail\DefaultAnalyzerResult;
use Drupal\inmail\MIME\MimeMessageInterface;
use Drupal\inmail\ProcessorResultInterface;
use Drupal\node\Entity\Node;
use Drupal\mailhandler\Plugin\inmail\Handler\MailhandlerNode;

/**
 * Message handler that creates a node from a mail message.
 *
 * To trigger this handler, the email subject needs to begin with
 * "[node][{content_type}]" pattern. It will be parsed by Entity type analyzer
 * and only if "node" entity type is identified this handler will execute.
 * The content type (bundle) can be pre-configured in the handler configuration
 * too.
 * Later on, this handler will authenticat and authorize a user based on the
 * analyzed result.
 * In case all the conditions above are met, a new node is created.
 *
 * @Handler(
 *   id = "dv_mailhandler_node",
 *   label = @Translation("Email Content"),
 *   description = @Translation("Creates a node from a mail message.")
 * )
 */
class DvMailhandlerNode extends MailhandlerNode {

  /**
   * {@inheritdoc}
   */
  public function invoke(MimeMessageInterface $message, ProcessorResultInterface $processor_result) {
    try {
      $result = $processor_result->getAnalyzerResult();

      if (!$result->hasContext('entity_type') || $result->getContext('entity_type')->getContextValue()['entity_type'] != 'node') {
        // Do not run this handler in case
        // the identified entity type is not node.
        return;
      }

      // Create a node.
      $node = $this->createNode($message, $result);

      \Drupal::logger('mailhandler')->log(RfcLogLevel::NOTICE, "\"{$node->label()}\" has been created by \"{$result->getAccount()->getDisplayName()}\".");
    }
    catch (\Exception $e) {
      // Log error in case verification, authentication or authorization fails.
      \Drupal::logger('mailhandler')->log(RfcLogLevel::WARNING, $e->getMessage());
    }
  }

  /**
   * Creates a new node from given mail message.
   *
   * @param \Drupal\inmail\MIME\MimeMessageInterface $message
   *   The mail message.
   * @param \Drupal\inmail\DefaultAnalyzerResult $result
   *   The analyzer result.
   *
   * @return \Drupal\node\Entity\Node
   *   The created node.
   *
   * @throws \Exception
   *   Throws an exception in case user is not authorized to create a node.
   */
  protected function createNode(MimeMessageInterface $message, DefaultAnalyzerResult $result) {

    $node = Node::create([
      'type' => $this->getContentType($result),
      'body' => [
        'value' => $result->getBody(),
        'format' => 'full_html',
      ],
      'uid' => 1,
      'title' => $result->getSubject(),
    ]);
    $node->save();

    return $node;
  }

  /**
   * Returns the content type.
   *
   * @param \Drupal\inmail\DefaultAnalyzerResult $result
   *   The analyzer result.
   *
   * @return string
   *   The content type.
   *
   * @throws \Exception
   *   Throws an exception in case user is not authorized to create a node.
   */
  protected function getContentType(DefaultAnalyzerResult $result) {
    $content_type = $this->configuration['content_type'];
    $node = TRUE;
    if ($content_type == '_mailhandler' && $result->hasContext('entity_type')) {
      $node = $result->getContext('entity_type')->getContextValue()['entity_type'] == 'node';
      $content_type = $result->getContext('entity_type')->getContextValue()['bundle'];
    }

    if (!$content_type || !$node) {
      throw new \Exception('Failed to process the message. The content type does not exist or node entity type is not specified.');
    }

    return $content_type;
  }

}
