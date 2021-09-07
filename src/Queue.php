<?php

namespace Drupal\dennis_link_checker;

use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Queue\DatabaseQueue;

/**
 * Class Queue.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Queue extends DatabaseQueue implements QueueInterface {

  /**
   * Remove any items from the queue that have been claimed but not deleted.
   */
  public function prune() {
    $this->connection->delete('queue')
      ->condition('name', $this->name)
      ->condition('expire', 0, '>')
      ->execute();
  }

}
