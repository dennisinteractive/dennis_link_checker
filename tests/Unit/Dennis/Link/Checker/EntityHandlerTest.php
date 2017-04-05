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
 * Class ItemTest
 * @package Dennis/Link/Checker
 */
class ItemTest extends PHPUnitTestCase {

  /**
   * @covers ::getLinksFromText
   * @dataProvider getLinksFromTextProvider
   */
  public function testGetLinksFromText($data) {
    $handler = new EntityHandler();
    $site_host = 'www.theweek.co.uk';
    $field_name = 'field_data_body';
    $entity_type = 'node';
    $entity_id = 123;

    $links = $handler->getLinksFromText($data['text'], $entity_type, $entity_id, $field_name, $site_host);

    foreach ($data['links'] as $k => $v) {
      $this->assertEquals($v, $links[$k]->originalSrc());
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
    $handler = new EntityHandler();
    $site_host = 'www.theweek.co.uk';
    $field_name = 'field_data_body';
    $entity_type = 'node';
    $entity_id = 123;

    $links = $handler->getLinksFromText($data['text'], $entity_type, $entity_id, $field_name, $site_host);
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

}
