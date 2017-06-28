<?php
/**
 * @file
 * Entity
 */
namespace Dennis\Link\Checker;

/**
 * Class Entity
 * @package Dennis\Link\Checker
 */
class Entity implements EntityInterface {
  /**
   * @var string
   */
  protected $entityType;

  /**
   * @var int
   */
  protected $entityId;

  /**
   * @var ConfigInterface
   */
  protected $config;

  /**
   * @inheritDoc
   */
  public function __construct($config, $entity_type, $entity_id) {
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
    return new Field($this, $field_name);
  }
}
