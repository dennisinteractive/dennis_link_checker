<?php

namespace Drupal\dennis_link_checker\Unit\Dennis\Link\Checker;

use Drupal\Tests\UnitTestCase;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Config;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Logger;
use Drupal\dennis_link_checker\Dennis\Link\Checker\LinkLocalisation;


/**
 * Class ConfigTest
 *
 * @package Drupal\dennis_link_checker\Unit\Dennis\Link\Checker
 * @group Link_checker
 */
class ConfigTest extends UnitTestCase {

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::getSiteHost
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::setSiteHost
   */
  public function testGetSiteHost() {
    $config = $this->config()->setSiteHost('foo.com');
    $this->assertEquals('foo.com', $config->getSiteHost());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::getMaxRedirects
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::setMaxRedirects
   */
  public function testGetMaxRedirects() {
    $config = $this->config()->setMaxRedirects(1234);
    $this->assertEquals('1234', $config->getMaxRedirects());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::internalOnly
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::setInternalOnly
   */
  public function testInternalOnly() {
    $config = $this->config()->setInternalOnly(TRUE);
    $this->assertEquals(TRUE, $config->internalOnly());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::getLocalisation
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::setLocalisation
   */
  public function testGetLocalisation() {
    $config = $this->config()->setLocalisation(LinkLocalisation::RELATIVE);
    $this->assertEquals(LinkLocalisation::RELATIVE, $config->getLocalisation());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::getLogger
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::setLogger
   */
  public function testGetLogger() {
    $logger = $this->getMockBuilder(Logger::class)->getMock();
    $config = $this->config()->setLogger($logger);
    $this->assertInstanceOf(Logger::class, $config->getLogger());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::removeTermLinks
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::setRemoveTermLinks
   */
  public function testRemoveTermLinks() {
    $config = $this->config()->setRemoveTermLinks(TRUE);
    $this->assertEquals(TRUE, $config->removeTermLinks());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::getNodeList
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::setNodeList
   */
  public function testGetNodeList() {
    $config = $this->config()->setNodeList([1,2,3]);
    $this->assertEquals([1,2,3], $config->getNodeList());
  }

  /**
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::getFieldNames
   * @covers \Drupal\dennis_link_checker\Dennis\Link\Checker\Config::setFieldNames
   */
  public function testGetFieldNames() {
    $config = $this->config()->setNodeList(['a', 'b', 'c']);
    $this->assertEquals(['a', 'b', 'c'], $config->getNodeList());
  }

  /**
   * Returns an Article object.
   *
   * @return \Drupal\dennis_link_checker\Dennis\Link\Checker\Config
   */
  protected function config() {
    return new Config();
  }

}
