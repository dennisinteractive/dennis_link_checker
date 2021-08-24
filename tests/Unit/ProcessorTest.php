<?php

namespace Drupal\Tests\dennis_link_checker\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Config;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Logger;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Processor;
use Drupal\dennis_link_checker\Dennis\Link\Checker\RequestTimeoutException;

/**
 * Class ProcessorTest.
 *
 * @coversDefaultClass \Drupal\dennis_link_checker\Dennis\Link\Checker\Processor
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 * @group Link_checker
 */
class ProcessorTest extends UnitTestCase {

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Processor::run
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
      ->setMethods(
        ['doNextItem',
          'ensureEnqueued',
          'prune', 'inMaintenanceMode'
        ])
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
