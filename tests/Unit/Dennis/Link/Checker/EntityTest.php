<?php

namespace Drupal\dennis_link_checker\Unit\Dennis\Link\Checker;

use Drupal\Tests\UnitTestCase;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Field;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Config;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Entity;
use \Drupal\Core\Database\Connection;


/**
 * Class EntityTest
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 * @group Link_checker
 */
class EntityTest extends UnitTestCase {

  /**
   * @var Config
   */
  protected $config;

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->connection = $this->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->config = $this->getMockBuilder(Config::class)->getMock();
  }


  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Entity::getConfig
   */
  public function testGetConfig() {
    $entity = new Entity($this->connection, $this->config, 'node', '1');
    $this->assertInstanceOf(Config::class, $entity->getConfig());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Entity::entityType
   */
  public function testEntityType() {
    $entity = new Entity($this->connection, $this->config, 'node', '1');
    $this->assertEquals('node', $entity->entityType());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Entity::entityId
   */
  public function testEntityId() {
    $entity = new Entity($this->connection, $this->config, 'node', '1');
    $this->assertEquals('1', $entity->entityId());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Entity::getField
   */
  public function testGetField() {
    $entity = new Entity($this->connection, $this->config, 'node', '1');
    $field = $entity->getField('body');
    $this->assertInstanceOf(Field::class, $field);
  }

}
