<?php

namespace Drupal\dennis_link_checker;

/**
 * Class QueueInterface.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface QueueInterface {

  /**
   * Removes old items from the queue.
   */
  public function prune();

}
