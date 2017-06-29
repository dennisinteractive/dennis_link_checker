<?php
namespace Dennis\Link\Checker;


use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 *
 * @package Dennis/Link/Checker
 */
class ConfigTest extends PHPUnitTestCase {

  /**
   * @covers ::getSiteHost
   * @covers ::setSiteHost
   */
  public function testGetSiteHost() {
    $config = (new Config())->setSiteHost('foo.com');
    $this->assertEquals('foo.com', $config->getSiteHost());
  }

  /**
   * @covers ::getMaxRedirects
   * @covers ::setMaxRedirects
   */
  public function testGetMaxRedirects() {
    $config = (new Config())->setMaxRedirects(1234);
    $this->assertEquals('1234', $config->getMaxRedirects());
  }

  /**
   * @covers ::internalOnly
   * @covers ::setInternalOnly
   */
  public function testInternalOnly() {
    $config = (new Config())->setInternalOnly(TRUE);
    $this->assertEquals(TRUE, $config->internalOnly());
  }

  /**
   * @covers ::getLocalisation
   * @covers ::setLocalisation
   */
  public function testGetLocalisation() {
    $config = (new Config())->setLocalisation(LinkLocalisation::RELATIVE);
    $this->assertEquals(LinkLocalisation::RELATIVE, $config->getLocalisation());
  }

  /**
   * @covers ::getLogger
   * @covers ::setLogger
   */
  public function testGetLogger() {
    $config = (new Config())->setLogger($this->getMock(Logger::class));
    $this->assertInstanceOf(Logger::class, $config->getLogger());
  }

  /**
   * @covers ::removeTermLinks
   * @covers ::setRemoveTermLinks
   */
  public function testRemoveTermLinks() {
    $config = (new Config())->setRemoveTermLinks(TRUE);
    $this->assertEquals(TRUE, $config->removeTermLinks());
  }

  /**
   * @covers ::getNodeList
   * @covers ::setNodeList
   */
  public function testGetNodeList() {
    $config = (new Config())->setNodeList([1,2,3]);
    $this->assertEquals([1,2,3], $config->getNodeList());
  }

  /**
   * @covers ::getFieldNames
   * @covers ::setFieldNames
   */
  public function testGetFieldNames() {
    $config = (new Config())->setNodeList(['a', 'b', 'c']);
    $this->assertEquals(['a', 'b', 'c'], $config->getNodeList());
  }

}
