<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Interface DatabaseInterface.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface DatabaseInterface {

  /**
   * Keep database connection alive.
   */
  public function keepConnectionAlive();

}
