<?php
/**
 * @file
 * ThrottlerInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface ThrottlerInterface
 * @package Dennis\Link\Checker
 */
interface ThrottlerInterface {
  /**
   * Waits until throttle period has passed.
   *
   * @param int $seconds
   */
  public function throttle($seconds);
}
