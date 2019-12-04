<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Core\Database\Connection;
use Drupal\dennis_link_checker\Dennis\CheckerManagers;

/**
 * Class Entity
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Entity implements EntityInterface {

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * @var CheckerManagers
   */
  protected $checker_managers;

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
   * Entity constructor.
   * @param Connection $connection
   * @param CheckerManagers $checkerManagers
   * @param $config
   * @param $entity_type
   * @param $entity_id
   */
  public function __construct(Connection $connection,
                              CheckerManagers $checkerManagers,
                              $config,
                              $entity_type,
                              $entity_id) {
    $this->connection = $connection;
    $this->checker_managers = $checkerManagers;
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
    return new Field(
      $this,
      $this->connection,
      $this->checker_managers,
      $field_name
    );
  }
}
