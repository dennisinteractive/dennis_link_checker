<?php
/**
 * @file ItemInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface EntityHandlerInterface
 * @package Dennis\Link\Checker
 */
interface EntityHandlerInterface {

  /**
   * The entity id & type.
   * @return EntityHandlerInterface
   */
  public function findLinks($entity_type, $id);

}
