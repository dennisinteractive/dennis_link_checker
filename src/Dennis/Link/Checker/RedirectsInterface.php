<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Interface RedirectsInterface
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface RedirectsInterface {

  /**
   * Load multiple URL redirects from the database by {redirect}.source.
   *
   * @param $source
   *   The source of the URL redirect.
   * @param $language
   *   Language of the source URL.
   * @param $query
   *   Array of URL query parameters.
   * @param $enabled_only
   *   Boolean that indicates whether to only load enabled redirects.
   */
  public function redirectLoadBySource($source, $language = LANGUAGE_NONE, array $query = [], $enabled_only = TRUE);

  /**
   * Load multiple URL redirects from the database.
   *
   * @param $rids
   *   An array of redirect IDs.
   */
  public function redirectLoadMultiple($rids = []);
  /**
   * Fetches multiple URL redirect IDs from the database by {redirect}.source.
   *
   * @param $source
   *   The source of the URL redirect.
   * @param $language
   *   Language of the source URL.
   * @param $enabled_only
   *   Boolean that indicates whether to only load enabled redirects.
   *
   */
  public function redirectFetchRidsByPath($source, $language, $enabled_only = FALSE);

  /**
   * Compare that all values and associations in one array match another array.
   *
   * We cannot use array_diff_assoc() here because we need to be recursive.
   *
   * @param $match
   *   The array that has the values.
   * @param $haystack
   *   The array that will be searched for values.
   */
  public function redirectCompareArrayRecursive($match, $haystack);
}
