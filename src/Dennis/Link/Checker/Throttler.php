<?php
/**
 * @file
 * Throttler
 */
namespace Dennis\Link\Checker;

/**
 * Class Throttler
 * @package Dennis\Link\Throttler
 */
class Throttler implements ThrottlerInterface {
  /**
   * @var
   */
  protected $startTime = 0;

  /**
   * @var int number of seconds to throttle for.
   */
  protected $seconds;

  /**
   * Throttler constructor.
   * @param $seconds
   */
  public function __construct($seconds) {
    $this->seconds = $seconds;
  }

  /**
   * @inheritDoc
   */
  public function throttle() {
    $wait_until = $this->startTime + $this->seconds;
    if (microtime(TRUE) < $wait_until) {
      usleep(($wait_until - microtime(TRUE)) * 1000000);
    }
    $this->startTime = microtime(TRUE);
  }
}
