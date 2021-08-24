<?php

namespace Drupal\Tests\dennis_link_checker\Unit;

use Drupal\Component\Utility\Html;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\Database\Connection;
use Drupal\dennis_link_checker\CheckerManagers;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Config;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Link;
use Drupal\dennis_link_checker\Dennis\Link\Checker\LinkLocalisation;

/**
 * Class LinkTest.
 *
 * @coversDefaultClass \Drupal\dennis_link_checker\Dennis\Link\Checker\Link
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 * @group Link_checker
 */
class LinkTest extends UnitTestCase {

  /**
   * Database connection instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Link checker.
   *
   * @var \Drupal\dennis_link_checker\CheckerManagers
   */
  protected $checkerManagers;

  /**
   * Setup mock objects.
   */
  public function setup() {
    parent::setUp();
    $this->connection = $this->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->checkerManagers = $this->getMockBuilder(CheckerManagers::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Link::correctedHref
   * @dataProvider getCorrectedHrefOriginalProvider
   *
   * @param array $data
   *   Data.
   */
  public function testCorrectedHrefOriginal(array $data) {
    $config = (new Config())
      ->setLocalisation(LinkLocalisation::ORIGINAL);

    $element = $this->getMockBuilder('\DOMElement')
      ->disableOriginalConstructor()
      ->getMock();

    $link = new Link($this->checkerManagers, $config, $data['in'], $element);

    $link->setFoundUrl($data['found']);
    $this->assertEquals($data['in'], $link->originalHref());
    $this->assertEquals($data['out'], $link->correctedHref());
  }

  /**
   * Data provider for testCorrectedHrefOriginal().
   */
  public function getCorrectedHrefOriginalProvider() {
    return [
      [[
        'in' => 'http://example.com/foo',
        'found' => 'http://example.com/bar',
        'out' => 'http://example.com/bar'
      ]
      ],
      [[
        'in' => 'https://example.com/foo',
        'found' => 'https://example.com/foo',
        'out' => 'https://example.com/foo'
      ]
      ],
      [[
        'in' => '/foo',
        'found' => 'http://example.com/bar',
        'out' => '/bar'
      ]
      ],
    ];
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Link::correctedHref
   * @dataProvider getCorrectedHrefAbsoluteProvider
   *
   * @param array $data
   *   Data.
   */
  public function testCorrectedHrefAbsolute(array $data) {
    $config = (new Config())
      ->setSiteHost('www.theweek.co.uk')
      ->setLocalisation(LinkLocalisation::ABSOLUTE);

    $element = $this->getMockBuilder('\DOMElement')
      ->disableOriginalConstructor()
      ->getMock();

    $link = new Link($this->checkerManagers, $config, $data['in'], $element);
    $link->setFoundUrl($data['found']);
    $this->assertEquals($data['in'], $link->originalHref());
    $this->assertEquals($data['out'], $link->correctedHref());
  }

  /**
   * Data provider for testCorrectedHrefAbsolute().
   */
  public function getCorrectedHrefAbsoluteProvider() {
    return [
      [[
        'in' => 'http://example.com/foo',
        'found' => 'http://example.com/foo',
        'out' => 'http://example.com/foo'
      ]
      ],
      [[
        'in' => 'https://example.com/foo',
        'found' => 'https://example.com/foo',
        'out' => 'https://example.com/foo'
      ]
      ],
      [[
        'in' => '/foo',
        'found' => 'http://example.com/foo',
        'out' => 'http://example.com/foo'
      ]
      ],
    ];
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Link::correctedHref
   * @dataProvider getCorrectedHrefRelativeProvider
   *
   * @param array $data
   *   Data.
   */
  public function testCorrectedHrefRelative(array $data) {
    $config = (new Config())
      ->setSiteHost('www.theweek.co.uk')
      ->setLocalisation(LinkLocalisation::RELATIVE);

    $element = $this->getMockBuilder('\DOMElement')
      ->disableOriginalConstructor()
      ->getMock();

    $link = new Link($this->checkerManagers, $config, $data['in'], $element);
    $link->setFoundUrl($data['found']);
    $this->assertEquals($data['in'], $link->originalHref());
    $this->assertEquals($data['out'], $link->correctedHref());
  }

  /**
   * Data provider for testCorrectedHrefRelative().
   */
  public function getCorrectedHrefRelativeProvider() {
    return [
      [[
        'in' => 'http://example.com/foo',
        'found' => 'http://example.com/foo',
        'out' => 'http://example.com/foo'
      ]
      ],
      [[
        'in' => 'https://example.com/foo',
        'found' => 'https://example.com/foo',
        'out' => 'https://example.com/foo'
      ]
      ],
      [[
        'in' => '/foo',
        'found' => 'http://www.theweek.co.uk/foo',
        'out' => '/foo'
      ]
      ],
      [[
        'in' => '//foo',
        'found' => 'http://www.theweek.co.uk/foo',
        'out' => '/foo'
      ]
      ],
      [[
        'in' => '/foo',
        'found' => 'https://www.theweek.co.uk/foo',
        'out' => '/foo'
      ]
      ],
    ];
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Link::correctedHref
   * @dataProvider getCorrectedHrefProtocolProvider
   *
   * @param array $data
   *   Data.
   */
  public function testCorrectedHrefProtocol(array $data) {
    $config = (new Config())
      ->setSiteHost('www.theweek.co.uk')
      ->setLocalisation(LinkLocalisation::PROTOCOL_RELATIVE);

    $element = $this->getMockBuilder('\DOMElement')
      ->disableOriginalConstructor()
      ->getMock();

    $link = new Link($this->checkerManagers, $config, $data['in'], $element);

    $link->setFoundUrl($data['found']);
    $this->assertEquals($data['in'], $link->originalHref());
    $this->assertEquals($data['out'], $link->correctedHref());
  }

  /**
   * Data provider for testCorrectedHrefProtocol().
   */
  public function getCorrectedHrefProtocolProvider() {
    return [
      [[
        'in' => 'http://example.com/foo',
        'found' => 'http://example.com/foo',
        'out' => 'http://example.com/foo'
      ]
      ],
      [[
        'in' => 'https://example.com/foo',
        'found' => 'https://example.com/foo',
        'out' => 'https://example.com/foo'
      ]
      ],
      [[
        'in' => '/foo',
        'found' => 'http://www.theweek.co.uk/foo',
        'out' => '//foo'
      ]
      ],
      [[
        'in' => '//foo',
        'found' => 'http://www.theweek.co.uk/foo',
        'out' => '//foo'
      ]
      ],
      [[
        'in' => '/foo',
        'found' => 'https://www.theweek.co.uk/foo',
        'out' => '//foo'
      ]
      ],
    ];
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Link::relativePath
   * @dataProvider getRelativePathProvider
   *
   * @param array $data
   *   Data.
   */
  public function testRelativePath(array $data) {
    $config = (new Config())
      ->setSiteHost('www.theweek.co.uk')
      ->setLocalisation(LinkLocalisation::RELATIVE);

    $field = $this->getMockBuilder('Dennis\Link\Checker\Field')
      ->disableOriginalConstructor()
      ->setMethods(['getConfig'])
      ->getMock();
    $field->method('getConfig')->willReturn($config);

    $element = $this->getMockBuilder('\DOMElement')
      ->disableOriginalConstructor()
      ->getMock();

    $link = new Link($this->checkerManagers, $config, 'foo', $element);

    $this->assertEquals($data['out'], $link->relativePath($data['in']));
  }

  /**
   * Data provider for testRelativePath().
   */
  public function getRelativePathProvider() {
    return [
      [[
        'in' => ['path' => '/foo', 'query' => 'a=b', 'fragment' => 'bar'],
        'out' => '/foo?a=b#bar'
      ]
      ],
      [[
        'in' => ['path' => '/foo', 'query' => 'a=b'],
        'out' => '/foo?a=b'
      ]
      ],
      [[
        'in' => ['path' => '/foo'],
        'out' => '/foo'
      ]
      ],
      [[
        'in' => ['host' => 'example.com', 'path' => '/foo'],
        'out' => '/foo'
      ]
      ],
      [[
        'in' => ['query' => 'a=b'],
        'out' => '?a=b'
      ]
      ],
    ];
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Link::strip
   * @dataProvider getStripProvider
   *
   * @param array $data
   *   Data.
   */
  public function testStrip(array $data) {
    // Get all the links from the text and strip 'em.
    $dom = Html::load($data['text']);
    $els = $dom->getElementsByTagName('a');
    $config = $this->getMockBuilder(Config::class)->getMock();

    $links = [];
    foreach ($els as $linkElement) {
      $links[] = new Link($this->checkerManagers, $config, $linkElement->getAttribute('href'), $linkElement);
      // Do not strip yet as php gets lost when deletions happen in foreach.
    }

    foreach ($links as $link) {
      $link->strip();
    }

    $this->assertEquals($data['out'], Html::serialize($dom));
  }

  /**
   * Data provider for testStrip().
   */
  public function getStripProvider() {
    return [
      // Standard link replacement.
      [[
        'text' => 'Foo <a href="http://example.com">example</a> bar',
        'out' => 'Foo example bar'
      ]
      ],
      // Link with multiple lines.
      [[
        'text' => 'Foo <a 
        href="http://example.com"
        >example
        </a> bar',
        'out' => 'Foo example
         bar'
      ]
      ],
      // Multiple links with the same href.
      [[
        'text' => 'Foo <a href="http://example.com">example 1</a> bar <a href="http://example.com">example 2</a> foo',
        'out' => 'Foo example 1 bar example 2 foo'
      ]
      ],
      // Multiple links with the same href on multiple lines.
      [[
        'text' => 'Foo <a href="http://example.com">example 1</a> bar 
        new line <a href="http://example.com">example 
        another new line</a> foo',
        'out' => 'Foo example 1 bar 
        new line example 
        another new line foo'
      ]
      ],
      // Href on invalid element.
      [[
        'text' => 'Foo <p href="http://example.com">example</p> bar',
        'out' => 'Foo <p href="http://example.com">example</p> bar'
      ]
      ],
      // Multiple links with the same href.
      [[
        'text' => 'Foo <a href="http://example.com">example 1</a> bar <a href="http://example.com/foo">example 2</a> foo',
        'out' => 'Foo example 1 bar example 2 foo'
      ]
      ],
      // Child elements are kept.
      [[
        'text' => 'Foo <a href="http://example.com"><span>An image <img src="image.png" /></span></a> bar',
        'out' => 'Foo <span>An image <img src="image.png" /></span> bar'
      ]
      ],
    ];
  }

}
