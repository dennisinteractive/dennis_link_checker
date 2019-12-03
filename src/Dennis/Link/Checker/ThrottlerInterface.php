<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Interface ThrottlerInterface
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface ThrottlerInterface {
  /**
   * Waits until throttle period has passed.
   */
  public function throttle();
}
