<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Core\Database\Connection;

/**
 * Class Database.
 *
 * @package Dennis\Link\Database
 */
class Database implements DatabaseInterface {

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * How often the DB should be pinged in seconds.
   */
  const INTERVAL = 15;

  /**
   * @var int last time the DB was pinged.
   */
  protected $pingTime = 0;

  /**
   * Database constructor.
   *
   * @param Connection $connection
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * @inheritDoc
   */
  public function keepConnectionAlive() {
    $now = time();
    // If it's been more than 15 seconds... Ping!
    if (($now - $this->pingTime) > self::INTERVAL) {
      $this->connection->query('SELECT CURTIME()');
      // Set the ping time.
      $this->pingTime = $now;
    }
  }
}
