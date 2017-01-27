<?php

namespace Drupal\dv_mailhandler\Plugin\inmail\Analyzer;

use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\inmail\DefaultAnalyzerResult;
use Drupal\inmail\MIME\MimeMessageInterface;
use Drupal\inmail\Plugin\inmail\Analyzer\AnalyzerBase;
use Drupal\inmail\ProcessorResultInterface;

/**
 * Finds the sender based on "From" mail header field.
 *
 * This analyzer extracts the email address from "From" mail header field and
 * based on this information finds the corresponding user. As this option is not
 * entirely safe, it is disabled by default.
 *
 * @ingroup analyzer
 *
 * @Analyzer(
 *   id = "to",
 *   label = @Translation("To Analyzer")
 * )
 */
class ToAnalyzer extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(MimeMessageInterface $message, ProcessorResultInterface $processor_result) {
    $result = $processor_result->getAnalyzerResult();

    $this->findTo($message, $result);
  }

  /**
   * Finds message receiver.
   *
   * @param \Drupal\inmail\MIME\MimeMessageInterface $message
   *   The mail message.
   * @param \Drupal\inmail\DefaultAnalyzerResult $result
   *   The analyzer result.
   */
  protected function findTo(MimeMessageInterface $message, DefaultAnalyzerResult $result) {
    $to = $message->getTo()[0]->getAddress();

    // Add to context.
    $context_definition = new ContextDefinition('any', $this->t('To context'));
    $context = new Context($context_definition, $to);
    $result->setContext('to', $context);
  }

}