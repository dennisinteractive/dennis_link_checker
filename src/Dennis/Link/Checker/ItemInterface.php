<?php
/**
 * @file ItemInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface ItemInterface
 * @package Dennis\Link\Checker
 */
interface ItemInterface {

  /**
   * ItemInterface constructor.
   * @param $entity_type
   * @param $entity_id
   */
  public function __construct($entity_type, $entity_id);

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

}
