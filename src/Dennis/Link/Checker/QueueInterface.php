<?php
/**
 * @file
 * QueueInterface
 */
namespace Dennis\Link\Checker;

/**
 * Class QueueInterface
 * @package Dennis\Link\Checker
 */
interface QueueInterface {
  /**
   * Removes old items from the queue.
   */
  public function prune();

  /**
   * Removes all items from the queue.
   */
  public function removeAll();

  /**
   * Get the number of items in the Link Checker queueueue.
   *
   * @return int
   *   A count of the number of queued items.
   */
  public function count();
}
