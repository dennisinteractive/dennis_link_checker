<?php
/**
 * @file
 * AnalyzerInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface AnalyzeInterface
 * @package Dennis\Link\Checker
 */
interface AnalyzerInterface {

  /**
   * AnalyzerInterface constructor.
   *
   * @param ConfigInterface $config
   * @param Throttler $curl_throttler
   * @param Database $database
   */
  public function __construct(ConfigInterface $config, Throttler $curl_throttler, Database $database);

  /**
   * Checks the link.
   *
   * @param $link LinkInterface
   * @return string
   * @thows RequestTimeoutException
   */
  public function link(LinkInterface $link);

  /**
   * Checks an array of links.
   *
   * @param array $links
   *   An array of LinkInterface links
   * @return array
   */
  public function multipleLinks($links);

  /**
   * Gets the host domain of the site.
   *
   * @return string
   */
  public function getSiteHost();

}
