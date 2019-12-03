<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Condition;

/**
 * Class Redirects
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Redirects implements RedirectsInterface {

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * Redirects constructor.
   * @param Connection $connection
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * @param $source
   *   The source of the URL redirect.
   * @param $language
   *   Language of the source URL.
   * @param $query
   *   Array of URL query parameters.
   * @param $enabled_only
   *   The first matched URL redirect object, or FALSE if there aren't any.
   * @return bool|\Drupal\Core\Entity\EntityInterface|mixed
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function redirectLoadBySource($source, $language = LANGUAGE_NONE, array $query = [], $enabled_only = TRUE) {
    $rids = $this->redirectFetchRidsByPath($source, $language, $enabled_only);

    if ($rids && $redirects = $this->redirectLoadMultiple($rids)) {
      // Narrow down the list of candidates.
      foreach ($redirects as $rid => $redirect) {
        if (!empty($redirect->source_options['query'])) {
          if (empty($query) || !$this->redirectCompareArrayRecursive($redirect->source_options['query'], $query)) {
            unset($redirects[$rid]);
            continue;
          }
        }

        // Add a case sensitive matches condition to be used in sorting.
        if ($source !== $redirect->source) {
          $redirects[$rid]->weight = 1;
        }
      }

      if (!empty($redirects)) {
        // Sort the redirects in the proper order.
        uasort($redirects, '_redirect_uasort');

        // Allow other modules to alter the redirect candidates before selecting the top one.
        $context = ['language' => $language, 'query' => $query];
        \Drupal::moduleHandler()->alter('redirect_load_by_source', $redirects, $source, $context);
        return !empty($redirects) ? reset($redirects) : FALSE;
      }
    }

    return FALSE;
  }

  /**
   * Load multiple URL redirects from the database.
   *
   * @param array $rids
   *   An array of redirect IDs.
   * @return \Drupal\Core\Entity\EntityInterface[] An array of URL redirect objects indexed by redirect IDs.
   *   An array of URL redirect objects indexed by redirect IDs.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function redirectLoadMultiple($rids = []) {

    // Using the storage controller (recommended).
    return \Drupal::entityTypeManager()->getStorage('redirect')->loadMultiple($rids);
  }


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
   * @return array
   *   An indexed array of IDs, or an empty array if there is no result set.
   */
  public function redirectFetchRidsByPath($source, $language, $enabled_only = FALSE) {
    static $status_field_exists = NULL;
    if (!isset($status_field_exists)) {
      $status_field_exists = $this->connection->schema()->fieldExists('redirect', 'status');
    }

    // Run a case-insensitive query for matching RIDs first.

    $rid_query = $this->connection->select('redirect');
    $rid_query->addField('redirect', 'rid');
    if ($enabled_only && $status_field_exists) {
      $rid_query->condition('status', 1);
    }

    if ($source !=  \Drupal::config('system.site')->get('page.front')) {
      $rid_query->condition('source', $rid_query->escapeLike($source), 'LIKE');
    }
    else {
      $source_condition = new Condition('OR');
      $source_condition->condition('source', $rid_query->escapeLike($source), 'LIKE');
      $source_condition->condition('source', '');
      $rid_query->condition($source_condition);
    }
    $rid_query->condition('language', [$language, LANGUAGE_NONE]);
    $rid_query->addTag('redirect_fetch');
    $rids = $rid_query->execute()->fetchCol();
    return $rids;
  }


  /**
   * Compare that all values and associations in one array match another array.
   *
   * We cannot use array_diff_assoc() here because we need to be recursive.
   *
   * @param $match
   *   The array that has the values.
   * @param $haystack
   *   The array that will be searched for values.
   * @return
   *   TRUE if all the elements of $match were found in $haystack, or FALSE
   *   otherwise.
   */
  public function redirectCompareArrayRecursive($match, $haystack) {
    foreach ($match as $key => $value) {
      if (!array_key_exists($key, $haystack)) {
        return FALSE;
      }
      elseif (is_array($value)) {
        if (!is_array($haystack[$key])) {
          return FALSE;
        }
        elseif (!$this->redirectCompareArrayRecursive($value, $haystack[$key])) {
          return FALSE;
        }
      }
      elseif ($value != $haystack[$key]) {
        return FALSE;
      }
    }
    return TRUE;
  }
}
