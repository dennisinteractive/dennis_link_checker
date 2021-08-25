<?php

namespace Drupal\Tests\dennis_link_checker\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Link;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Analyzer;
use Drupal\dennis_link_checker\Dennis\Link\Checker\ResourceFailException;
use Drupal\dennis_link_checker\Dennis\Link\Checker\RequestTimeoutException;

/**
 * Class AnalyzerTest.
 *
 * @coversDefaultClass \Drupal\dennis_link_checker\Dennis\Link\Checker\Analyzer
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 * @group Link_checker
 */
class AnalyzerTest extends UnitTestCase {

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Analyzer::getInfo
   */
  public function testGetInfo() {
    $analyzer = $this->getMockBuilder(Analyzer::class)
      ->disableOriginalConstructor()
      ->setMethods(['doInfoRequest'])
      ->getMock();

    // Mock the method that does the http request.
    $data = ['http_code' => '200'];
    $analyzer->method('doInfoRequest')->willReturn($data);

    // Check the static cache works,
    // by insuring the method that makes the actual request is called only once.
    $url = 'http://example.com';
    $analyzer->expects($this->once())->method('doInfoRequest')->with($url);
    $this->assertEquals($data, $analyzer->getInfo($url));
    $this->assertEquals($data, $analyzer->getInfo($url));
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Analyzer::getInfo
   * @expectedException \Drupal\dennis_link_checker\Dennis\Link\Checker\ResourceFailException
   */
  public function testGetInfoException() {
    // Check the exception is thrown.
    $analyzer = $this->getMockBuilder(Analyzer::class)
      ->disableOriginalConstructor()
      ->setMethods(['doInfoRequest'])
      ->getMock();
    $data = $this->getMockBuilder(ResourceFailException::class)
      ->disableOriginalConstructor()
      ->getMock();
    $analyzer->method('doInfoRequest')->willThrowException($data);
    $url = 'http://example.com';
    $analyzer->getInfo($url);
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Analyzer::link
   * @expectedException \Drupal\dennis_link_checker\Dennis\Link\Checker\RequestTimeoutException
   */
  public function testLinkException() {
    $analyzer = $this->getMockBuilder(Analyzer::class)
      ->disableOriginalConstructor()
      ->setMethods(['doInfoRequest', 'throttle', 'getSiteHost'])
      ->getMock();
    $data = $this->getMockBuilder(RequestTimeoutException::class)
      ->disableOriginalConstructor()
      ->getMock();
    $analyzer->method('throttle')->willReturn(TRUE);
    $analyzer->method('getSiteHost')->willReturn('example.com');
    $analyzer->method('doInfoRequest')->willThrowException($data);

    $link = $this->getMockBuilder(Link::class)
      ->disableOriginalConstructor()
      ->getMock();
    $analyzer->link($link);
  }

}
