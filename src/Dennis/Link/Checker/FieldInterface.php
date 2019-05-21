<?php
/**
 * @file
 * FieldInterface
 */
namespace Dennis\Link\Checker;

/**
 * Class Field
 * @package Dennis\Link\Checker
 */
interface FieldInterface {
  /**
   * @return \Dennis\Link\Checker\EntityInterface
   */
  public function getEntity();

  /**
   * Get links from field.
   * @return array \Dennis\Link\Checker\LinkInterface
   */
  public function getLinks();

  /**
   * Saves the field.
   */
  public function save();

  /**
   * @return \Dennis\Link\Checker\Config
   */
  public function getConfig();
}
