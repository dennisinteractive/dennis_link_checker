<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Class Entity
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
Interface EntityInterface {

  /**
   * Entity ID.
   *
   * @return int
   */
  public function entityId();

  /**
   * Entity Type
   *
   * @return string
   */
  public function entityType();

  /**
   * Get config.
   *
   * @return ConfigInterface
   */
  public function getConfig();

  /**
   * Returns the requested field.
   *
   * @param string $field_name
   * @return Field
   */
  public function getField($field_name);
}
