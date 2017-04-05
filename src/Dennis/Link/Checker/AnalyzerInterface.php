<?php
/**
 * @file AnalyzerInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface AnalyzeInterface
 * @package Dennis\Link\Checker
 */
interface AnalyzerInterface {

  /**
   * Checks the link.
   *
   * @param $link LinkInterface
   * @return string
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
   * The host domain of the site.
   * @param string $host
   * @return EntityHandlerInterface
   */
  public function setSiteHost($host);

  /**
   * Gets the host domain of the site.
   *
   * @return string
   */
  public function getSiteHost();

}
