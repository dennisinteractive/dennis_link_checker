<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Class Field
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface FieldInterface {
  /**
   * @return EntityInterface
   */
  public function getEntity();

  /**
   * Get links from field.
   * @return array
   */
  public function getLinks();

  /**
   * Saves the field.
   */
  public function save();

  /**
   * @return Config
   */
  public function getConfig();
}
