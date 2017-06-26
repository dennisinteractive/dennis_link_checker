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
   * @param $field_name
   */
  public function __construct($entity_type, $entity_id, $field_name);

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
   * The field name.
   * @return string
   */
  public function fieldName();
}
