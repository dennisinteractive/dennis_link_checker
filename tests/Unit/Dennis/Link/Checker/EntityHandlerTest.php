<?php
/**
 * @file
 * Test
 */
namespace Dennis\Link\Checker;

// Use our mocked versions of some global functions.
include 'global_functions.php';

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class EntityHandlerTest
 * @package Dennis/Link/Checker
 */
class EntityHandlerTest extends PHPUnitTestCase {

  /**
   * @covers ::getLinksFromText
   * @dataProvider getLinksFromTextProvider
   */
  public function testGetLinksFromText($data) {
    $config = (new Config())->setSiteHost('www.theweek.co.uk');

    $field_name = 'field_data_body';

    $handler = new EntityHandler($config);
    $entity_type = 'node';
    $entity_id = 123;

    $links = $handler->getLinksFromText($data['text'], $entity_type, $entity_id, $field_name);

    foreach ($data['links'] as $k => $v) {
      $this->assertEquals($v, $links[$k]->originalHref());
    }
  }

  /**
   * Data provider for testGetLinksFromText();
   */
  public function getLinksFromTextProvider() {
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
   * @covers ::getLinksFromText
   * @dataProvider getExternalLinksFromTextProvider
   */
  public function testExternalGetLinksFromText($data) {
    $config = (new Config())->setSiteHost('www.theweek.co.uk');
    $handler = new EntityHandler($config);

    $field_name = 'field_data_body';
    $entity_type = 'node';
    $entity_id = 123;

    $links = $handler->getLinksFromText($data['text'], $entity_type, $entity_id, $field_name);
    $this->assertEmpty($links);
  }

  /**
   * Data provider for testExternalGetLinksFromText();
   */
  public function getExternalLinksFromTextProvider() {
    return [
      [['text' => '<a href="http://example.com">example</a>']],
      [['text' => '<a href="https://example.com">example</a>']],
      // Some links a just plain wrong. We don't fix bad html.
      [['text' => '<a href="%20http://www.theweek.co.uk/foo">foo</a>']],
      [['text' => '<a href="%22http://www.theweek.co.uk/foo">foo</a>']],
    ];
  }

  /**
   * @covers ::stripLinks
   * @dataProvider getStripLinksProvider
   */
  public function testStripLinks($data) {
    //$config = $this->getMockBuilder(ConfigInterface::class)->getMock();
    $config = (new Config())
      ->setSiteHost('www.theweek.co.uk')
      ->setLogger((new Logger())->setVerbosity(Logger::VERBOSITY_LOW));
    $handler = new EntityHandler($config);

    $entity_type = 'node';
    $entity_id = 123;
    $field = 'foo';
    $link = new Link($config, $entity_type, $entity_id, $field, $data['href']);

    $out = $handler->stripLink($link, $data['text']);
    $this->assertEquals($data['out'], $out);
  }

  /**
   * Data provider for testStripLinks();
   */
  public function getStripLinksProvider() {
    return [
      // Standard link replacement.
      [['text' => 'Foo <a href="http://example.com">example</a> bar',
        'href' => 'http://example.com',
        'out' => 'Foo example bar']],
      // Link with multiple lines.
      [['text' => 'Foo <a 
        href="http://example.com"
        >example
        </a> bar',
        'href' => 'http://example.com',
        'out' => 'Foo example
         bar']],
      // Multiple links with the same href.
      [['text' => 'Foo <a href="http://example.com">example 1</a> bar <a href="http://example.com">example 2</a> foo',
        'href' => 'http://example.com',
        'out' => 'Foo example 1 bar example 2 foo']],
      // Multiple links with the same href on multiple lines.
      [['text' => 'Foo <a href="http://example.com">example 1</a> bar 
        new line <a href="http://example.com">example 
        another new line</a> foo',
        'href' => 'http://example.com',
        'out' => 'Foo example 1 bar 
        new line example 
        another new line foo']],
      //Href on invalid element.
      [['text' => 'Foo <p href="http://example.com">example</p> bar',
        'href' => 'http://example.com',
        'out' => 'Foo <p href="http://example.com">example</p> bar']],
      // Multiple links with the same href.
      [['text' => 'Foo <a href="http://example.com">example 1</a> bar <a href="http://example.com/foo">example 2</a> foo',
        'href' => 'http://example.com',
        'out' => 'Foo example 1 bar example 2 foo']],
      ];

  }

}
