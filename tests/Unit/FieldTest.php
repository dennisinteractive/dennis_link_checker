<?php

namespace Drupal\Tests\dennis_link_checker\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\Component\Utility\Html;
use \Drupal\Core\Database\Connection;
use Drupal\dennis_link_checker\CheckerManagers;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Field;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Config;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Logger;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Entity;


/**
 * Class FieldTest
 *
 * @coversDefaultClass \Drupal\dennis_link_checker\Dennis\Link\Checker\Field
 *
 * @package Drupal\dennis_link_checker\Unit\Dennis\Link\Checker
 * @group Link_checker
 */
class FieldTest extends UnitTestCase {
  /**
   * @var Field
   */
  protected $field;

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * @var CheckerManagers
   */
  protected $checker_managers;

  /**
   * Setup mock objects.
   */
  public function setup() {

    parent::setUp();

    $this->connection = $this->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->checker_managers = $this->getMockBuilder(CheckerManagers::class)
      ->disableOriginalConstructor()
      ->getMock();

    $config = (new Config())
      ->setSiteHost('www.theweek.co.uk')
      ->setLogger((new Logger())->setVerbosity(Logger::VERBOSITY_LOW));

    $entity = $this->getMockBuilder(Entity::class)
      ->disableOriginalConstructor()
      ->setMethods(['getConfig'])
      ->getMock();
    $entity->method('getConfig')->willReturn($config);

    $this->field = $this->getMockBuilder(Field::class)
      ->setConstructorArgs([$entity, $this->connection, $this->checker_managers, 'body'])
      ->setMethods(['getDOM'])
      ->getMock();
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Field::getLinks
   * @dataProvider getLinksProvider
   */
  public function testGetLinks($data) {
    $this->field->method('getDOM')->willReturn(Html::load($data['text']));
    $links = $this->field->getLinks();

    foreach ($data['links'] as $k => $v) {
      $this->assertEquals($v, $links[$k]->originalHref());
    }
  }

  /**
   * Data provider for testGetLinks();
   */
  public function getLinksProvider() {
    return [
      //
      [['text' => 'notorious <a href="http://www.theweek.co.uk/foo" target="_self">encounter</a> in ',
        'links' => ['http://www.theweek.co.uk/foo'],
      ]],
      [['text' => '<a href="/bar" target="_self">b</a>',
        'links' => ['/bar'],
      ]],
      [['text' => '<a href="/foo">foo</a> & <a href="/bar">bar</a>',
        'links' => ['/foo', '/bar'],
      ]],

    ];
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Field::getLinks
   * @dataProvider getExternalLinksProvider
   */
  public function testExternalGetLinks($data) {
    $this->field->method('getDOM')->willReturn(Html::load($data['text']));
    $links = $this->field->getLinks();
    $this->assertEmpty($links);
  }

  /**
   * Data provider for testExternalGetLinks();
   */
  public function getExternalLinksProvider() {
    return [
      [['text' => '<a href="http://example.com">example</a>']],
      [['text' => '<a href="https://example.com">example</a>']],
      // Some links a just plain wrong. We don't fix bad html.
      [['text' => '<a href="%20http://www.theweek.co.uk/foo">foo</a>']],
      [['text' => '<a href="%22http://www.theweek.co.uk/foo">foo</a>']],
    ];
  }
}
