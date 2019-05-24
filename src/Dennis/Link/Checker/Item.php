<?php
/**
 * @file
 * Item
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
  public function __construct($entity_type, $entity_id, $entity_vid, $field_name) {
    $this->data['entity_id'] = $entity_id;
    $this->data['entity_vid'] = $entity_vid;
    $this->data['entity_type'] = $entity_type;
    $this->data['field_name'] = $field_name;
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

  /**
   * @inheritDoc
   */
  public function entityVid() {
    return $this->data['entity_vid'];
  }

  /**
   * @inheritDoc
   */
  public function fieldName() {
    // Return field name if specified, or body by default.
    return !empty($this->data['field_name']) ? $this->data['field_name'] : 'body';
  }

  /**
   * @inheritDoc
   */
  public function recordItemProcessed() {
    if (!db_table_exists('dennis_link_checker_checked_nodes')) {
      die('Database schema not installed for dennis_link_checker - please run database updates.');
    }

    $result = db_merge('dennis_link_checker_checked_nodes')
    ->key([
      'nid' => $this->entityId(),
      'vid' => $this->entityVid(),
      'field_name' => $this->fieldName(),
    ])
    ->fields([
      'last_checked' => time(),
    ])->execute();
  }
}
