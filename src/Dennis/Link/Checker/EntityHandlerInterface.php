<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Interface EntityHandlerInterface.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface EntityHandlerInterface {

  /**
   * The configuration object.
   *
   * @param ConfigInterface $config
   *   Config interface.
   */
  public function setConfig(ConfigInterface $config);

  /**
   * Gets the host domain of the site.
   */
  public function getSiteHost();

  /**
   * Return the requested Entity.
   *
   * @param string $entity_type
   *   Entity type.
   * @param int $entity_id
   *   Entity id.
   */
  public function getEntity($entity_type, $entity_id);

}
