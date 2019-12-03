<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Core\Database\Connection;

/**
 * Class EntityHandler
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class EntityHandler implements EntityHandlerInterface {

  /**
   * @var ConfigInterface
   */
  protected $config;

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * @inheritDoc
   */
  public function __construct(ConfigInterface $config, Connection $connection) {
    $this->config = $config;
    $this->connection = $connection;
  }

  /**
   * @inheritDoc
   */
  public function setConfig(ConfigInterface $config) {
    $this->config = $config;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getSiteHost() {
    return $this->config->getSiteHost();
  }

  /**
   * @inheritDoc
   */
  public function getEntity($entity_type, $entity_id) {
    return new Entity($this->connection, $this->config, $entity_type, $entity_id);
  }
}
