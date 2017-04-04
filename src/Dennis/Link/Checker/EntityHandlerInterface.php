<?php
/**
 * @file EntityHandlerInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface EntityHandlerInterface
 * @package Dennis\Link\Checker
 */
interface EntityHandlerInterface {

  /**
   * The entity id & type.
   * @return array of LinkInterface
   */
  public function findLinks($entity_type, $id);

  /**
   * The host domain of the site.
   * @param string $host
   * @return EntityHandlerInterface
   */
  public function setSiteHost($host);

  /**
   * Gets the host domain of the site.
   *
   * @return string
   */
  public function getSiteHost();

}
