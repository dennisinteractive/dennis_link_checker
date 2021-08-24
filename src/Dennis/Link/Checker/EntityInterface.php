<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Class Entity.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface EntityInterface {

  /**
   * Entity ID.
   *
   * @return int
   *   Returns the entity id.
   */
  public function entityId();

  /**
   * Entity Type.
   *
   * @return string
   *   Returns the entity type.
   */
  public function entityType();

  /**
   * Get config.
   *
   * @return ConfigInterface
   *   Returns the config interface.
   */
  public function getConfig();

  /**
   * Returns the requested field.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return Field
   *   Returns a new field with the given name.
   */
  public function getField($field_name);

}
