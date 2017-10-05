<?php
/**
 * @file
 * Tests for Analyzer
 */
namespace Dennis\Link\Checker;

// Use our mocked versions of some global functions.
include_once 'global_functions.php';

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class FieldTest
 * @package Dennis/Link/Checker
 */
class AnalyzerTest extends PHPUnitTestCase {

  /**
   * @covers ::getInfo
   */
  public function testGetLinks() {
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
   * @expectedException \Exception
   */
  public function testGetLinksException() {
    // Check the exception is thrown.
    $analyzer = $this->getMockBuilder(Analyzer::class)
      ->disableOriginalConstructor()
      ->setMethods(['doInfoRequest'])
      ->getMock();
    $data = new \Exception('test', 42);
    $analyzer->method('doInfoRequest')->willThrowException($data);
    $url = 'http://example.com';
    $analyzer->getInfo($url);
  }

}
