<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Class Throttler.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Throttler implements ThrottlerInterface {

  /**
   * Start time.
   *
   * @var int
   */
  protected $startTime = 0;

  /**
   * Number of seconds to throttle for.
   *
   * @var int
   */
  protected $seconds;

  /**
   * Throttler constructor.
   *
   * @param string $seconds
   *   Number of seconds.
   */
  public function __construct($seconds) {
    $this->seconds = $seconds;
  }

  /**
   * {@inheritDoc}
   */
  public function throttle() {
    $wait_until = $this->startTime + $this->seconds;
    if (microtime(TRUE) < $wait_until) {
      usleep(($wait_until - microtime(TRUE)) * 1000000);
    }
    $this->startTime = microtime(TRUE);
  }

}
