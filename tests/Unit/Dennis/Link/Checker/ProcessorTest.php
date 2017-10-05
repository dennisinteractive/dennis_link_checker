<?php
/**
 * @file
 * Tests for Processor
 */
namespace Dennis\Link\Checker;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;


/**
 * Class ProcessorTest
 * @package Dennis/Link/Checker
 */
class ProcessorTest extends PHPUnitTestCase {

  /**
   * @covers ::run
   */
  public function testRun() {

    $logger = $this->getMockBuilder(Logger::class)
      ->disableOriginalConstructor()
      ->setMethods(['warning'])
      ->getMock();
    $logger->method('warning')->willReturn(TRUE);

    $config = $this->getMockBuilder(Config::class)
      ->disableOriginalConstructor()
      ->setMethods(['getLogger'])
      ->getMock();
    $config->method('getLogger')->willReturn($logger);

    $proc = $this->getMockBuilder(Processor::class)
      ->disableOriginalConstructor()
      ->setMethods(['doNextItem', 'ensureEnqueued', 'prune', 'inMaintenanceMode'])
      ->getMock();
    $proc->setConfig($config);
    $proc->method('ensureEnqueued')->willReturn(TRUE);
    $proc->method('prune')->willReturn(TRUE);
    $proc->method('inMaintenanceMode')->willReturn(FALSE);

    // Check it finishes cleanly when there are no more items left.
    $proc->method('doNextItem')->willReturn(FALSE);
    $this->assertTrue($proc->run());

    // Check that the processor returns false on RequestTimeoutException.
    $e = new RequestTimeoutException('timeout test', CURLOPT_TIMEOUT);
    $proc->method('doNextItem')->willThrowException($e);
    $this->assertFalse($proc->run());

    // Check that it cannot run in maintenance mode.
    $proc->method('inMaintenanceMode')->willReturn(TRUE);
    $this->assertFalse($proc->run());

  }

}
