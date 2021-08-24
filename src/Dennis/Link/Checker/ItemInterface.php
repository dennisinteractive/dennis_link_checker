<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Interface ItemInterface.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface ItemInterface {

  /**
   * ItemInterface constructor.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $entity_id
   *   Entity id.
   * @param string $field_name
   *   Field name.
   */
  public function __construct($entity_type, $entity_id, $field_name);

  /**
   * The type of the entity.
   *
   * @return string
   *   The entity type string.
   */
  public function entityType();

  /**
   * The entity id.
   *
   * @return int
   *   The entity id.
   */
  public function entityId();

  /**
   * The field name.
   *
   * @return string
   *   The field name.
   */
  public function fieldName();

}
