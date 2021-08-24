<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Class Database.
 *
 * @package Dennis\Link\Database
 */
class Database implements DatabaseInterface {

  /**
   * How often the DB should be pinged in seconds.
   */
  const INTERVAL = 15;

  /**
   * Last time the DB was pinged.
   *
   * @var int
   */
  protected $pingTime = 0;

  /**
   * {@inheritDoc}
   */
  public function keepConnectionAlive() {
    $now = time();
    // If it's been more than 15 seconds... Ping!
    if (($now - $this->pingTime) > self::INTERVAL) {
      // Set the ping time.
      $this->pingTime = $now;
    }
  }

}
