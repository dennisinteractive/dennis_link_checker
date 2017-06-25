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
}
