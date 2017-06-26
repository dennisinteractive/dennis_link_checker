<?php
/**
 * @file
 * Queue
 */
namespace Dennis\Link\Checker;

/**
 * Class Queue
 * @package Dennis\Link\Checker
 */
class Queue extends \SystemQueue implements QueueInterface {
  /**
   * Remove any items from the queue that have been claimed but not deleted.
   */
  public function prune() {
    db_delete('queue')
      ->condition('name', $this->name)
      ->condition('expire', 0, '>')
      ->execute();
  }
}
