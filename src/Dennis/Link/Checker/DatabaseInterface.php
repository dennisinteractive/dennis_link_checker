<?php
/**
 * @file
 * DatabaseInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface DatabaseInterface
 * @package Dennis\Link\Checker
 */
interface DatabaseInterface {
  /**
   * Keep database connection alive.
   */
  public function keepConnectionAlive();
}
