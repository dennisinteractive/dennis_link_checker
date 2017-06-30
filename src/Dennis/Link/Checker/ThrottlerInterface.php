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
   */
  public function throttle();
}
