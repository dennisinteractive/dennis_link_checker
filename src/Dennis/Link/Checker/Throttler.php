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
   * @inheritDoc
   */
  public function throttle($seconds) {
    $wait_until = $this->startTime + $seconds;
    if (microtime(TRUE) < $wait_until) {
      usleep(($wait_until - microtime(TRUE)) * 1000000);
    }
    $this->startTime = microtime(TRUE);
  }
}
