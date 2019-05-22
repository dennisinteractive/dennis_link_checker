<?php
/**
 * @file
 * Throttler
 */
namespace Dennis\Link\Checker;

/**
 * Class Throttler
 * @package Dennis\Link\Checker
 */
class Throttler implements ThrottlerInterface {
  /**
   * @var
   */
  protected $startTime = 0;

  /**
   * @var int number of seconds to throttle for.
   */
  protected $milliseconds;

  /**
   * Throttler constructor.
   *
   * @param $milliseconds
   */
  public function __construct($milliseconds) {
    $this->milliseconds = $milliseconds;
  }

  /**
   * @inheritDoc
   */
  public function throttle() {
    $wait_until = $this->startTime + ($this->milliseconds / 1000);
    if (microtime(TRUE) < $wait_until) {
      usleep(($wait_until - microtime(TRUE)) * 1000000);
    }
    $this->startTime = microtime(TRUE);
  }
}
