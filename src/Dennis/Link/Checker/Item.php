<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Class Item.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Item implements ItemInterface {


  /**
   * Protected data array.
   *
   * @var array
   */
  protected $data = [];

  /**
   * {@inheritDoc}
   */
  public function __construct($entity_type, $entity_id, $field_name) {
    $this->data['entity_id'] = $entity_id;
    $this->data['entity_type'] = $entity_type;
    $this->data['field_name'] = $field_name;
  }

  /**
   * {@inheritDoc}
   */
  public function entityType() {
    return $this->data['entity_type'];
  }

  /**
   * {@inheritDoc}
   */
  public function entityId() {
    return $this->data['entity_id'];
  }

  /**
   * {@inheritDoc}
   */
  public function fieldName() {
    // Return field name if specified, or body by default.
    return !empty($this->data['field_name']) ? $this->data['field_name'] : 'body';
  }

}
