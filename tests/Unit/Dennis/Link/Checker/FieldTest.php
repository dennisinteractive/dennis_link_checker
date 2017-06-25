<?php
/**
 * @file
 * Tests for Field
 */
namespace Dennis\Link\Checker;

// Use our mocked versions of some global functions.
include_once 'global_functions.php';

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class FieldTest
 * @package Dennis/Link/Checker
 */
class FieldTest extends PHPUnitTestCase {
  /**
   * @var Field
   */
  protected $field;

  /**
   * Setup mock objects.
   */
  public function setup() {
    $config = (new Config())
      ->setSiteHost('www.theweek.co.uk')
      ->setLogger((new Logger())->setVerbosity(Logger::VERBOSITY_LOW));

    $entity = $this->getMockBuilder('Dennis\Link\Checker\Entity')
      ->disableOriginalConstructor()
      ->getMock();
    $entity->method('getConfig')->willReturn($config);

    $this->field = $this->getMockBuilder('Dennis\Link\Checker\Field')
      ->setConstructorArgs(array($entity, 'body'))
      ->setMethods(array('getDOM'))
      ->getMock();
  }

  /**
   * @covers ::getLinks
   * @dataProvider getLinksProvider
   */
  public function testGetLinks($data) {
    $this->field->method('getDOM')->willReturn(filter_dom_load($data['text']));
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
   * @covers ::getLinks
   * @dataProvider getExternalLinksProvider
   */
  public function testExternalGetLinks($data) {
    $this->field->method('getDOM')->willReturn(filter_dom_load($data['text']));
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
