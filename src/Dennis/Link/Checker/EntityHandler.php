<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Core\Database\Connection;
use Drupal\dennis_link_checker\CheckerManagers;

/**
 * Class EntityHandler
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class EntityHandler implements EntityHandlerInterface {

  /**
   * @var ConfigInterface
   */
  protected $config;

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * @var CheckerManagers
   */
  protected $checker_managers;

  /**
   * EntityHandler constructor.
   *
   * @param ConfigInterface $config
   * @param Connection $connection
   * @param CheckerManagers $checkerManagers
   */
  public function __construct(ConfigInterface $config,
                              Connection $connection,
                              CheckerManagers $checkerManagers) {
    $this->config = $config;
    $this->connection = $connection;
    $this->checker_managers = $checkerManagers;
  }

  /**
   * @inheritDoc
   */
  public function setConfig(ConfigInterface $config) {
    $this->config = $config;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getSiteHost() {
    return $this->config->getSiteHost();
  }

  /**
   * @inheritDoc
   */
  public function getEntity($entity_type, $entity_id) {
    return new Entity(
      $this->connection,
      $this->checker_managers,
      $this->config,
      $entity_type,
      $entity_id
    );
  }
}
