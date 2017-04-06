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
   * AnalyzerInterface constructor.
   * @param ConfigInterface $config
   */
  public function __construct(ConfigInterface $config);

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
   * Gets the host domain of the site.
   *
   * @return string
   */
  public function getSiteHost();

}
