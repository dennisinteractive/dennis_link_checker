<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\dennis_link_checker\CheckerManagers;

/**
 * Class EntityHandler.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class EntityHandler implements EntityHandlerInterface {

  /**
   * Config interface.
   *
   * @var ConfigInterface
   */
  protected $config;

  /**
   * Checker managers.
   *
   * @var \Drupal\dennis_link_checker\CheckerManagers
   */
  protected $checkerManagers;

  /**
   * EntityHandler constructor.
   *
   * @param ConfigInterface $config
   *   Config interface.
   * @param \Drupal\dennis_link_checker\CheckerManagers $checkerManagers
   *   Checker managers.
   */
  public function __construct(ConfigInterface $config,
                              CheckerManagers $checkerManagers) {
    $this->config = $config;
    $this->checkerManagers = $checkerManagers;
  }

  /**
   * {@inheritDoc}
   */
  public function setConfig(ConfigInterface $config) {
    $this->config = $config;
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function getSiteHost() {
    return $this->config->getSiteHost();
  }

  /**
   * {@inheritDoc}
   */
  public function getEntity($entity_type, $entity_id) {
    return new Entity(
      $this->checkerManagers,
      $this->config,
      $entity_type,
      $entity_id
    );
  }

}
