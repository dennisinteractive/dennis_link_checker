<?php
/**
 * @file
 * Database
 */
namespace Dennis\Link\Checker;

/**
 * Class Database
 * @package Dennis\Link\Database
 */
class Database implements DatabaseInterface {
  /**
   * How often the DB should be pinged in seconds.
   */
  const interval = 15;

  /**
   * @var int last time the DB was pinged.
   */
  protected $pingTime = 0;

  /**
   * @inheritDoc
   */
  public function keepConnectionAlive() {
    $now = time();
    // If it's been more than 15 seconds... Ping!
    if (($now - $this->pingTime) > self::interval) {
      echo 'Keeping DB connection alive';
      db_query('SELECT CURTIME()');
      // Set the ping time.
      $this->pingTime = $now;
    }
  }
}
