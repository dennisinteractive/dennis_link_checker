<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Core\Database\Connection;

/**
 * Class Entity
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Entity implements EntityInterface {

  protected $connection;

  /**
   * @var ConfigInterface
   */
  protected $config;

  /**
   * @var string
   */
  protected $entityType;

  /**
   * @var int
   */
  protected $entityId;

  /**
   * @inheritDoc
   */
  public function __construct(Connection $connection, $config, $entity_type, $entity_id) {
    $this->connection = $connection;
    $this->config = $config;
    $this->entityType = $entity_type;
    $this->entityId = $entity_id;
  }

  /**
   * @inheritDoc
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * @inheritDoc
   */
  public function entityId() {
    return $this->entityId;
  }

  /**
   * @inheritDoc
   */
  public function entityType() {
    return $this->entityType;
  }

  /**
   * @inheritDoc
   */
  public function getField($field_name) {
    return new Field($this, $this->connection, $field_name);
  }
}
