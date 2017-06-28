<?php
/**
 * @file
 * Test
 */
namespace Dennis\Link\Checker;

// Use our mocked versions of some global functions.
include_once 'global_functions.php';

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class LinkTest
 * @package Dennis/Link/Checker
 */
class LinkTest extends PHPUnitTestCase {

  /**
   * @covers ::correctedHref
   * @dataProvider getCorrectedHrefOriginalProvider
   */
  public function testCorrectedHrefOriginal($data) {
    $config = (new Config())
      ->setLocalisation(LinkLocalisation::ORIGINAL);

    $element = $this->getMockBuilder('\DOMElement')
      ->disableOriginalConstructor()
      ->getMock();

    $link = new Link($config, $data['in'], $element);

    $link->setFoundUrl($data['found']);
    $this->assertEquals($data['in'], $link->originalHref());
    $this->assertEquals($data['out'], $link->correctedHref());
  }

  /**
   * Data provider for testCorrectedHrefOriginal();
   */
  public function getCorrectedHrefOriginalProvider() {
    return [
      [['in' => 'http://example.com/foo',
        'found' => 'http://example.com/bar',
        'out' => 'http://example.com/bar']],
      [['in' => 'https://example.com/foo',
        'found' => 'https://example.com/foo',
        'out' => 'https://example.com/foo']],
      [['in' => '/foo',
        'found' => 'http://example.com/bar',
        'out' => '/bar']],
    ];
  }

  /**
   * @covers ::correctedHref
   * @dataProvider getCorrectedHrefAbsoluteProvider
   */
  public function testCorrectedHrefAbsolute($data) {
    $config = (new Config())
      ->setSiteHost('www.theweek.co.uk')
      ->setLocalisation(LinkLocalisation::ABSOLUTE);

    $element = $this->getMockBuilder('\DOMElement')
      ->disableOriginalConstructor()
      ->getMock();

    $link = new Link($config, $data['in'], $element);
    $link->setFoundUrl($data['found']);
    $this->assertEquals($data['in'], $link->originalHref());
    $this->assertEquals($data['out'], $link->correctedHref());
  }

  /**
   * Data provider for testCorrectedHrefAbsolute();
   */
  public function getCorrectedHrefAbsoluteProvider() {
    return [
      [['in' => 'http://example.com/foo',
        'found' => 'http://example.com/foo',
        'out' => 'http://example.com/foo']],
      [['in' => 'https://example.com/foo',
        'found' => 'https://example.com/foo',
        'out' => 'https://example.com/foo']],
      [['in' => '/foo',
        'found' => 'http://example.com/foo',
        'out' => 'http://example.com/foo']],
    ];
  }

  /**
   * @covers ::correctedHref
   * @dataProvider getCorrectedHrefRelativeProvider
   */
  public function testCorrectedHrefRelative($data) {
    $config = (new Config())
      ->setSiteHost('www.theweek.co.uk')
      ->setLocalisation(LinkLocalisation::RELATIVE);

    $element = $this->getMockBuilder('\DOMElement')
      ->disableOriginalConstructor()
      ->getMock();

    $link = new Link($config, $data['in'], $element);
    $link->setFoundUrl($data['found']);
    $this->assertEquals($data['in'], $link->originalHref());
    $this->assertEquals($data['out'], $link->correctedHref());
  }

  /**
   * Data provider for testCorrectedHrefRelative();
   */
  public function getCorrectedHrefRelativeProvider() {
    return [
      [['in' => 'http://example.com/foo',
        'found' => 'http://example.com/foo',
        'out' => 'http://example.com/foo']],
      [['in' => 'https://example.com/foo',
        'found' => 'https://example.com/foo',
        'out' => 'https://example.com/foo']],
      [['in' => '/foo',
        'found' => 'http://www.theweek.co.uk/foo',
        'out' => '/foo']],
      [['in' => '//foo',
        'found' => 'http://www.theweek.co.uk/foo',
        'out' => '/foo']],
      [['in' => '/foo',
        'found' => 'https://www.theweek.co.uk/foo',
        'out' => '/foo']],
    ];
  }

  /**
   * @covers ::correctedHref
   * @dataProvider getCorrectedHrefProtocolProvider
   */
  public function testCorrectedHrefProtocol($data) {
    $config = (new Config())
      ->setSiteHost('www.theweek.co.uk')
      ->setLocalisation(LinkLocalisation::PROTOCOL_RELATIVE);

    $element = $this->getMockBuilder('\DOMElement')
      ->disableOriginalConstructor()
      ->getMock();

    $link = new Link($config, $data['in'], $element);

    $link->setFoundUrl($data['found']);
    $this->assertEquals($data['in'], $link->originalHref());
    $this->assertEquals($data['out'], $link->correctedHref());
  }

  /**
   * Data provider for testCorrectedHrefProtocol();
   */
  public function getCorrectedHrefProtocolProvider() {
    return [
      [['in' => 'http://example.com/foo',
        'found' => 'http://example.com/foo',
        'out' => 'http://example.com/foo']],
      [['in' => 'https://example.com/foo',
        'found' => 'https://example.com/foo',
        'out' => 'https://example.com/foo']],
      [['in' => '/foo',
        'found' => 'http://www.theweek.co.uk/foo',
        'out' => '//foo']],
      [['in' => '//foo',
        'found' => 'http://www.theweek.co.uk/foo',
        'out' => '//foo']],
      [['in' => '/foo',
        'found' => 'https://www.theweek.co.uk/foo',
        'out' => '//foo']],
    ];
  }

  /**
   * @covers ::relativePath
   * @dataProvider getRelativePathProvider
   */
  public function testRelativePath($data) {
    $config = (new Config())
      ->setSiteHost('www.theweek.co.uk')
      ->setLocalisation(LinkLocalisation::RELATIVE);

    $field = $this->getMockBuilder('Dennis\Link\Checker\Field')
      ->disableOriginalConstructor()
      ->getMock();
    $field->method('getConfig')->willReturn($config);

    $element = $this->getMockBuilder('\DOMElement')
      ->disableOriginalConstructor()
      ->getMock();

    $link = new Link($config, 'foo', $element);

    $this->assertEquals($data['out'], $link->relativePath($data['in']));
  }

  /**
   * Data provider for testRelativePath();
   */
  public function getRelativePathProvider() {
    return [
      [['in' => ['path' => '/foo', 'query' => 'a=b', 'fragment' => 'bar'],
        'out' => '/foo?a=b#bar']],
      [['in' => ['path' => '/foo', 'query' => 'a=b'],
        'out' => '/foo?a=b']],
      [['in' => ['path' => '/foo'],
        'out' => '/foo']],
      [['in' => ['host' => 'example.com', 'path' => '/foo'],
        'out' => '/foo']],
      [['in' => ['query' => 'a=b'],
        'out' => '?a=b']],
    ];
  }


  /**
   * @covers ::strip
   * @dataProvider getStripProvider
   */
  public function testStrip($data) {
    // Get all the links from the text and strip 'em.
    $dom = filter_dom_load($data['text']);
    $els = $dom->getElementsByTagName('a');
    $links = [];
    foreach ($els as $linkElement) {
      $links[] = new Link($this->getMock(Config::class), $linkElement->getAttribute('href'), $linkElement);
      // Do not strip yet as php gets lost when deletions happen in foreach
    }

    foreach ($links as $link) {
      $link->strip();
    }

    $this->assertEquals($data['out'], filter_dom_serialize($dom));
  }

  /**
   * Data provider for testStrip();
   */
  public function getStripProvider() {
    return [
      // Standard link replacement.
      [['text' => 'Foo <a href="http://example.com">example</a> bar',
        'out' => 'Foo example bar']],
      // Link with multiple lines.
      [['text' => 'Foo <a 
        href="http://example.com"
        >example
        </a> bar',
        'out' => 'Foo example
         bar']],
      // Multiple links with the same href.
      [['text' => 'Foo <a href="http://example.com">example 1</a> bar <a href="http://example.com">example 2</a> foo',
        'out' => 'Foo example 1 bar example 2 foo']],
      // Multiple links with the same href on multiple lines.
      [['text' => 'Foo <a href="http://example.com">example 1</a> bar 
        new line <a href="http://example.com">example 
        another new line</a> foo',
        'out' => 'Foo example 1 bar 
        new line example 
        another new line foo']],
      //Href on invalid element.
      [['text' => 'Foo <p href="http://example.com">example</p> bar',
        'out' => 'Foo <p href="http://example.com">example</p> bar']],
      // Multiple links with the same href.
      [['text' => 'Foo <a href="http://example.com">example 1</a> bar <a href="http://example.com/foo">example 2</a> foo',
        'out' => 'Foo example 1 bar example 2 foo']],
      // Child elements are kept.
      [['text' => 'Foo <a href="http://example.com"><span>An image <img src="image.png" /></span></a> bar',
        'out' => 'Foo <span>An image <img src="image.png" /></span> bar']],
    ];

  }

}
