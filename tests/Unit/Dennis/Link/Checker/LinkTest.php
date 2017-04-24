<?php
/**
 * @file
 * Test
 */
namespace Dennis\Link\Checker;

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
      ->setSiteHost('www.theweek.co.uk')
      ->setLocalisation(LinkLocalisation::ORIGINAL);

    $entity_type = 'node';
    $entity_id = 123;
    $field = 'foo';
    $link = new Link($config, $entity_type, $entity_id, $field, $data['in']);
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
        'found' => 'http://example.com/foo',
        'out' => 'http://example.com/foo']],
      [['in' => 'https://example.com/foo',
        'found' => 'https://example.com/foo',
        'out' => 'https://example.com/foo']],
      [['in' => '/foo',
        'found' => 'http://example.com/foo',
        'out' => '/foo']],
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

    $entity_type = 'node';
    $entity_id = 123;
    $field = 'foo';
    $link = new Link($config, $entity_type, $entity_id, $field, $data['in']);
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

    $entity_type = 'node';
    $entity_id = 123;
    $field = 'foo';
    $link = new Link($config, $entity_type, $entity_id, $field, $data['in']);
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

    $entity_type = 'node';
    $entity_id = 123;
    $field = 'foo';
    $link = new Link($config, $entity_type, $entity_id, $field, $data['in']);
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
    $config = $this->getMockBuilder(ConfigInterface::class)->getMock();
    $link = new Link($config, 'foo', 123, 'foo', 'foo');

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

}
