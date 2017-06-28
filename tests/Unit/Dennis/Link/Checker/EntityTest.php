<?php
/**
 * @file
 * Tests for Field
 */
namespace Dennis\Link\Checker;


use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class EntityTest
 * @package Dennis/Link/Checker
 */
class EntityTest extends PHPUnitTestCase {

  /**
   * @covers ::getConfig
   */
  public function testGetConfig() {
    $entity = new Entity($this->getMock(Config::class), 'node', '1');
    $this->assertInstanceOf(Config::class, $entity->getConfig());
  }

  /**
   * @covers ::entityType
   */
  public function testEntityType() {
    $entity = new Entity($this->getMock(Config::class), 'node', '1');
    $this->assertEquals('node', $entity->entityType());
  }

  /**
   * @covers ::entityId
   */
  public function testEntityId() {
    $entity = new Entity($this->getMock(Config::class), 'node', '1');
    $this->assertEquals('1', $entity->entityId());
  }

  /**
   * @covers ::getField
   */
  public function testGetField() {
    $entity = new Entity($this->getMock(Config::class), 'node', '1');
    $field = $entity->getField('body');
    $this->assertInstanceOf(Field::class, $field);
  }

}
