<?php
/**
 * @file
 * Tests for Analyzer
 */
namespace Dennis\Link\Checker;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class AnalyzerTest
 * @package Dennis/Link/Checker
 */
class AnalyzerTest extends PHPUnitTestCase {

  /**
   * @covers ::getInfo
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
   * @covers ::getInfo
   * @expectedException \Dennis\Link\Checker\ResourceFailException
   */
  public function testGetInfoException() {
    // Check the exception is thrown.
    $analyzer = $this->getMockBuilder(Analyzer::class)
      ->disableOriginalConstructor()
      ->setMethods(['doInfoRequest'])
      ->getMock();
    $data = new ResourceFailException('test', 42);
    $analyzer->method('doInfoRequest')->willThrowException($data);
    $url = 'http://example.com';
    $analyzer->getInfo($url);
  }

  /**
   * @covers ::link
   * @expectedException \Dennis\Link\Checker\RequestTimeoutException
   */
  public function testLinkException() {
    $analyzer = $this->getMockBuilder(Analyzer::class)
      ->disableOriginalConstructor()
      ->setMethods(['doInfoRequest', 'throttle', 'getSiteHost'])
      ->getMock();
    $data = new RequestTimeoutException('timeout test', CURLOPT_TIMEOUT);
    $analyzer->method('throttle')->willReturn(TRUE);
    $analyzer->method('getSiteHost')->willReturn('example.com');
    $analyzer->method('doInfoRequest')->willThrowException($data);

    $link = $this->getMockBuilder(Link::class)
      ->disableOriginalConstructor()
      ->getMock();
    $analyzer->link($link);
  }

}
