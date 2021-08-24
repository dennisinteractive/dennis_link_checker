<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Interface AnalyzeInterface.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface AnalyzerInterface {

  /**
   * AnalyzerInterface constructor.
   *
   * @param ConfigInterface $config
   *   Config interface.
   * @param Throttler $curl_throttler
   *   Throttler.
   * @param Database $database
   *   Database.
   */
  public function __construct(ConfigInterface $config, Throttler $curl_throttler, Database $database);

  /**
   * Checks the link.
   *
   * @param LinkInterface $link
   *   Link interface.
   *
   * @return string
   *   Returns the link string.
   *
   * @thows RequestTimeoutException
   */
  public function link(LinkInterface $link);

  /**
   * Checks an array of links.
   *
   * @param array $links
   *   An array of LinkInterface links.
   *
   * @return array
   *   Returns an array of multiple arrays.
   */
  public function multipleLinks(array $links);

  /**
   * Gets the host domain of the site.
   *
   * @return string
   *   Returns the site host.
   */
  public function getSiteHost();

}
