<?php

namespace Drupal\Tests\dennis_link_checker\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\Core\Database\Connection;
use Drupal\dennis_link_checker\Dennis\CheckerManagers;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Field;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Config;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Entity;



/**
 * Class EntityTest
 *
 * @coversDefaultClass \Drupal\dennis_link_checker\Dennis\Link\Checker\Entity
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
   * @var CheckerManagers
   */
  protected $checker_managers;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->connection = $this->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->config = $this->getMockBuilder(Config::class)->getMock();
    $this->checker_managers = $this->getMockBuilder(CheckerManagers::class)
      ->disableOriginalConstructor()
      ->getMock();
  }


  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Entity::getConfig
   */
  public function testGetConfig() {
    $entity = new Entity($this->connection, $this->checker_managers, $this->config, 'node', '1');
    $this->assertInstanceOf(Config::class, $entity->getConfig());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Entity::entityType
   */
  public function testEntityType() {
    $entity = new Entity($this->connection, $this->checker_managers, $this->config, 'node', '1');
    $this->assertEquals('node', $entity->entityType());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Entity::entityId
   */
  public function testEntityId() {
    $entity = new Entity($this->connection, $this->checker_managers, $this->config, 'node', '1');
    $this->assertEquals('1', $entity->entityId());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Entity::getField
   */
  public function testGetField() {
    $entity = new Entity($this->connection, $this->checker_managers, $this->config, 'node', '1');
    $field = $entity->getField('body');
    $this->assertInstanceOf(Field::class, $field);
  }
}
