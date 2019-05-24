<?php
/**
 * @file
 * Queue
 */

namespace Dennis\Link\Checker;

/**
 * Class Queue
 *
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

  /**
   * @inheritDoc
   */
  public function removeAll() {
    db_delete('queue')
      ->condition('name', $this->name)
      ->execute();
  }

  /**
   * @inheritDoc
   */
  public function count() {
    $query = db_select('queue')
      ->condition('name', $this->name)
      ->execute()
      ->fetchAll();

    $num_rows = $query->countQuery()->execute()->fetchField();

    return $num_rows;
  }
}
