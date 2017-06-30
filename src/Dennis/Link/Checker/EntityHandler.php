<?php
/**
 * @file
 * EntityHandler
 */
namespace Dennis\Link\Checker;

/**
 * Class EntityHandler
 * @package Dennis\Link\Checker
 */
class EntityHandler implements EntityHandlerInterface {

  protected $config;

  /**
   * @inheritDoc
   */
  public function __construct(ConfigInterface $config) {
    $this->config = $config;
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
    return new Entity($this->config, $entity_type, $entity_id);
  }
}
