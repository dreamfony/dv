<?php
namespace Drupal\dmt_demo_mailing_list\Queue;

use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Drupal\Core\Queue\SuspendQueueException;

class ProcessQueue {

  /**
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * @var \Drupal\Core\Queue\QueueWorkerManagerInterface
   */
  protected $queueManager;

  /**
   * ProcessQueue constructor.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue
   * @param \Drupal\Core\Queue\QueueWorkerManagerInterface $queue_manager
   */
  public function __construct(QueueFactory $queue, QueueWorkerManagerInterface $queue_manager) {
    $this->queueFactory = $queue;
    $this->queueManager = $queue_manager;
  }

  /**
   * Process queue.
   *
   * @param $queue_name
   */
  public function queueProcess($queue_name) {

    // we sleep here because activity queue workers expect items to be at least 5 seconds old
    sleep(6);

    // Get the queue implementation for import_content_from_xml queue
    $queue = $this->queueFactory->get($queue_name);
    // Get the queue worker
    $queue_worker = $this->queueManager->createInstance($queue_name);

    // Get the number of items
    $number_of_queue = $queue->numberOfItems();

    // Repeat $number_of_queue times
    for ($i = 0; $i < $number_of_queue; $i++) {
      // Get a queued item
      if ($item = $queue->claimItem()) {
        try {
          // Process it
          $queue_worker->processItem($item->data);
          // If everything was correct, delete the processed item from the queue
          $queue->deleteItem($item);
        }
        catch (SuspendQueueException $e) {
          // If there was an Exception trown because of an error
          // Releases the item that the worker could not process.
          // Another worker can come and process it
          $queue->releaseItem($item);
          break;
        }
      }
    }
  }

}
