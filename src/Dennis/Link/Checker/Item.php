<?php
/**
 * @file Item
 */
namespace Dennis\Link\Checker;

/**
 * Class Item
 * @package Dennis\Link\Checker
 */
class Item implements ItemInterface {

  protected $data = [];

  /**
   * @inheritDoc
   */
  public function __construct($entity_type, $entity_id) {
    $this->data['entity_id'] = $entity_id;
    $this->data['entity_type'] = $entity_type;
  }

  /**
   * @inheritDoc
   */
  public function entityType() {
    return $this->data['entity_type'];
  }

  /**
   * @inheritDoc
   */
  public function entityId() {
    return $this->data['entity_id'];
  }

}
