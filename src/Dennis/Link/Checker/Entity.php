<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\dennis_link_checker\CheckerManagers;

/**
 * Class Entity.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Entity implements EntityInterface {

  /**
   * Checker managers.
   *
   * @var \Drupal\dennis_link_checker\CheckerManagers
   */
  protected $checkerManagers;

  /**
   * Config interface.
   *
   * @var ConfigInterface
   */
  protected $config;

  /**
   * Entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * Entity id.
   *
   * @var int
   */
  protected $entityId;

  /**
   * Entity constructor.
   *
   * @param \Drupal\dennis_link_checker\CheckerManagers $checkerManagers
   *   Checker managers.
   * @param ConfigInterface $config
   *   Config.
   * @param string $entity_type
   *   Entity type.
   * @param string $entity_id
   *   Entity id.
   */
  public function __construct(CheckerManagers $checkerManagers,
                              ConfigInterface $config,
                              $entity_type,
                              $entity_id) {
    $this->checkerManagers = $checkerManagers;
    $this->config = $config;
    $this->entityType = $entity_type;
    $this->entityId = $entity_id;
  }

  /**
   * {@inheritDoc}
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * {@inheritDoc}
   */
  public function entityId() {
    return $this->entityId;
  }

  /**
   * {@inheritDoc}
   */
  public function entityType() {
    return $this->entityType;
  }

  /**
   * {@inheritDoc}
   */
  public function getField($field_name) {
    return new Field(
      $this,
      $this->checkerManagers,
      $field_name
    );
  }

}
