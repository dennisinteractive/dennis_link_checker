<?php
/**
 * @file
 * AnalyzerInterface
 */

namespace Dennis\Link\Checker;

/**
 * Interface AnalyzeInterface
 *
 * @package Dennis\Link\Checker
 */
interface AnalyzerInterface {

  /**
   * AnalyzerInterface constructor.
   *
   * @param ConfigInterface $config
   * @param Throttler $curl_throttler
   * @param Database $database
   * @param array $statistics
   */
  public function __construct(ConfigInterface $config, Throttler $curl_throttler, Database $database, array $statistics);

  /**
   * Checks the link.
   *
   * @param $link LinkInterface
   *
   * @return string
   * @thows RequestTimeoutException
   */
  public function link(LinkInterface $link);

  /**
   * Checks an array of links.
   *
   * @param array $links
   *   An array of LinkInterface links
   *
   * @return array
   */
  public function multipleLinks($links);

  /**
   * Gets the host domain of the site.
   *
   * @return string
   */
  public function getSiteHost();

  /**
   * Gets either the whole statistics array, or just the value of one key.
   *
   * If $array_key is not empty, it is assumed to be a string which maps to
   * a key in the statistics array. If the array key exists, its value is
   * returned; otherwise, NULL is returned.
   *
   * @param string|null $array_key
   *
   * @return mixed
   */
  public function getStatistics($array_key = NULL);

  /**
   * Sets either the whole statistics array, or just one key in the array.
   *
   * If the first parameter passed in is an array, the whole statistics array
   * will be replaced.
   *
   * If the first parameter is a string, this is assumed to be the statistics
   * array key, and the second parameter is treated as the value to be set in
   * the statistics array.
   *
   * @param array|string $statistics_or_array_key
   * @param string|null $value
   */
  public function setStatistics($statistics_or_array_key, $value = NULL);

  /**
   * Updates a Link Checker statistic.
   *
   * $label should match an array key in the $statistics array, e.g.
   * - 404s_found
   * - 404s_removed
   * - errors_encountered
   * ... etc.
   *
   * If $value is not provided or is NULL, it is assumed that $label is a
   * counter and we just want to increment that counter by one.
   *
   * If $value is numeric, we assume $label maps to a counter and increment
   * that statistic by the value of $value.
   *
   * If $value is not numeric, we assume we're recording a string, array, etc,
   * and $label is assumed to be an indexed array list.
   *
   * @see _dennis_link_checker_last_run_statistics_default()
   *
   * @param $label
   * @param null $value
   */
  public function updateStatistics($label, $value = NULL);
}
