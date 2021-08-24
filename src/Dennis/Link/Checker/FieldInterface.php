<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Class Field.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface FieldInterface {

  /**
   * Get entity.
   */
  public function getEntity();

  /**
   * Get links from field.
   */
  public function getLinks();

  /**
   * Saves the field.
   */
  public function save();

  /**
   * Get config.
   */
  public function getConfig();

}
