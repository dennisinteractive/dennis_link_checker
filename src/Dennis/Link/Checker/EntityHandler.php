<?php
/**
 * @file Item
 */
namespace Dennis\Link\Checker;

/**
 * Class EntityHandler
 * @package Dennis\Link\Checker
 */
class EntityHandler implements EntityHandlerInterface {

  /**
   * @inheritDoc
   */
  public function findLinks($entity_type, $id) {
    // TODO: Implement findLinks() method.


    return ['body' => 'foo <a href="/bar">bar</a>'];
  }

}
