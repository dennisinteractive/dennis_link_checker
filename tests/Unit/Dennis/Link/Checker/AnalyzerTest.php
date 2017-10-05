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
    $analyzer->method('doInfoRequest')->willReturn(['http_code' => '200']);

    $this->assertEquals(['http_code' => '200'], $analyzer->getInfo('http://example.com'));
  }

}
