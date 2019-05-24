<?php
/**
 * @file
 * ItemInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface ItemInterface
 * @package Dennis\Link\Checker
 */
interface ItemInterface {

  /**
   * ItemInterface constructor.
   *
   * @param $entity_type
   * @param $entity_id
   * @param $entity_vid
   * @param $field_name
   */
  public function __construct($entity_type, $entity_id, $entity_vid, $field_name);

  /**
   * The type of the entity.
   * @return string
   */
  public function entityType();

  /**
   * The entity id.
   * @return integer
   */
  public function entityId();

  /**
   * The entity revision id ("vid").
   * @return integer
   */
  public function entityVid();

  /**
   * The field name.
   * @return string
   */
  public function fieldName();

  /**
   * Mark this item updated in {dennis_link_checker_checked_nodes}.
   *
   * @return mixed
   */
  public function recordItemProcessed();
}
