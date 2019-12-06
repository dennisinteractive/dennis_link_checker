<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\dennis_link_checker\CheckerManagers;

/**
 * Class Entity
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Entity implements EntityInterface {

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
   * @param CheckerManagers $checkerManagers
   * @param $config
   * @param $entity_type
   * @param $entity_id
   */
  public function __construct(CheckerManagers $checkerManagers,
                              $config,
                              $entity_type,
                              $entity_id) {
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
      $this->checker_managers,
      $field_name
    );
  }
}
