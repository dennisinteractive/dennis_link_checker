<?php

/**
 * @file
 * dennis_link_checker.api.inc
 *
 * API functions for the Dennis Link Checker module.
 */

use Dennis\Link\Checker\Config;
use Dennis\Link\Checker\Logger;
use Dennis\Link\Checker\LinkLocalisation;
use Dennis\Link\Checker\Processor;
use Dennis\Link\Checker\EntityHandler;
use Dennis\Link\Checker\Analyzer;
use Dennis\Link\Checker\Throttler;
use Dennis\Link\Checker\Database;
use Dennis\Link\Checker\Queue;

/**
 * PSR-4 autoloader
 * files[] in .info doesn't find them.
 */
spl_autoload_register(function ($class) {
  if (strpos($class, 'Dennis\Link\Checker') === 0) {
    if (file_exists((__DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php'))) {
      include(__DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php');
    }
  }
});

/**
 * Setup the link processor.
 *
 * @param array $nids
 *
 * @return \Dennis\Link\Checker\ProcessorInterface
 */
function dennis_link_checker_setup(array $nids) {
  $site_host = parse_url(dennis_ga_get_live_url(), PHP_URL_HOST);
  $config = (new Config())
    ->setLogger((new Logger())->setVerbosity(Logger::VERBOSITY_HIGH))
    ->setSiteHost($site_host)
    ->setMaxRedirects(10)
    ->setInternalOnly(TRUE)
    ->setLocalisation(LinkLocalisation::ORIGINAL)
    ->setFieldNames(_dennis_link_checker_get_field_names())
    ->setNodeList($nids);

  $queue = new Queue('dennis_link_checker');
  $entity_handler = new EntityHandler($config);

  // Make sure we don't request more than one page per second.
  $curl_throttler = new Throttler(500);

  // Database object that allows interaction with the DB.
  $database = new Database();

  $analyzer = new Analyzer($config, $curl_throttler, $database, _dennis_link_checker_last_run_statistics_default());

  return new Processor($config, $queue, $entity_handler, $analyzer);
}

/**
 * Run the link checker queue.
 *
 * @param array $node_ids
 *   An array of node IDs to check.
 *
 * @param null|int $time_limit
 *   Override the time limit to X seconds.
 *
 * @return \Dennis\Link\Checker\ProcessorInterface
 */
function dennis_link_checker_run(array $node_ids = [], $time_limit = NULL) {
  // Set the "is running" flag and last started timestamp.
  variable_set(DENNIS_LINK_CHECKER_VARIABLE_IS_RUNNING, TRUE);

  // Note that we use time() and not REQUEST_TIME as these two values can vary
  // significantly for long-running batch tasks.
  variable_set(DENNIS_LINK_CHECKER_VARIABLE_LAST_STARTED_TIME, time());

  $processor = dennis_link_checker_setup($node_ids);

  // Remove any already-queued items.
  $processor->getQueue()->removeAll();

  if (!is_null($time_limit) && is_numeric($time_limit)) {
    $processor->setTimeLimit($time_limit);
  }
  else {
    $processor->setTimeLimit(variable_get(DENNIS_LINK_CHECKER_VARIABLE_TIME_LIMIT, DENNIS_LINK_CHECKER_TIME_LIMIT_DEFAULT));
  }

  // Run the processor.
  $processor->run();

  // Un-set the "is running" flag and set the last completed timestamp.
  // Note that we use time() and not REQUEST_TIME as these two values can vary
  // significantly for long-running batch tasks.
  variable_set(DENNIS_LINK_CHECKER_VARIABLE_IS_RUNNING, FALSE);
  variable_set(DENNIS_LINK_CHECKER_VARIABLE_LAST_COMPLETED_TIME, time());

  // Also reset the skip counter back to zero.
  variable_set(DENNIS_LINK_CHECKER_VARIABLE_NUMBER_RUNS_SKIPPED, DENNIS_LINK_CHECKER_NUMBER_RUNS_SKIPPED_DEFAULT);

  // Record statistics.
  $statistics_array_dump = $processor->getAnalyzer()->getStatistics();

  // At this point, $statistics_array_dump could be a MASSIVE array of arrays
  // - we don't want to try and stuff this into {variable} because Drupal will
  // choke, so we will turn it into counts only, but we might want to come back
  // at some point and find a way of exporting this data as a report, perhaps?

  // Turn the raw dump into a series of counts.
  $statistics_array = [];
  foreach ($statistics_array_dump as $key => $value) {
    if (is_array($value)) {
      $statistics_array[$key] = count($value);
    }
    else {
      $statistics_array[$key] = $value;
    }
  }

  $statistics_array['previous_run'] = _dennis_link_checker_last_run_statistics();

  // Don't allow a recursive loop of previous_run dataception to build up.
  if (!empty($statistics_array['previous_run']['previous_run'])) {
    unset($statistics_array['previous_run']['previous_run']);
  }

//  $statistics_array['number_nodes_checked'] = $processor->getNumberChecked();
//  $statistics_array['number_links_checked'] = $processor->getNumberChecked();
//  $statistics_array['links_updated'] = count($processor->getLinksUpdated());
//  $statistics_array['links_not_updated'] = count($processor->getLinksNotUpdated());
//  $statistics_array['links_deleted'] = count($processor->getLinksDeleted());
//  $statistics_array['errors_encountered'] = count($processor->getErrorsEncountered());
//  $statistics_array['404s_found'] = count($processor->getNotFounds());
//  $statistics_array['404s_removed'] = count($processor->getNotFoundsFixed());
//  $statistics_array['redirect_loops_found'] = count($processor->getRedirectLoopsFound());
//  $statistics_array['redirect_loops_removed'] = count($processor->getRedirectLoopsRemoved());

  variable_set(DENNIS_LINK_CHECKER_VARIABLE_LAST_RUN_STATISTICS, $statistics_array);

  return $processor;
}

/**
 * Get the configured array of field names to scan for broken links.
 *
 * @return array
 */
function _dennis_link_checker_get_field_names() {
  return explode(',', variable_get(DENNIS_LINK_CHECKER_VARIABLE_FIELDS_TO_CHECK, _dennis_link_checker_fields_to_check_default_field_names()));
}

/**
 * Get an array of skipped run counter options.
 *
 * @return array
 */
function _dennis_link_checker_get_skip_options() {
  return [1, 2, DENNIS_LINK_CHECKER_FORCE_RUN_AFTER_X_SKIPS_DEFAULT, 8, 16];
}

/**
 * Get an empty array for last run statistics.
 *
 * @return array
 */
function _dennis_link_checker_last_run_statistics_default() {
  /**
   * Last run statistics - array:
   * - number of nodes checked
   * - number of links checked
   * - number redirects updated successfully
   * - number errors encountered
   * - number 404s found
   * - number 404s fixed
   * - number redirect loops found
   * - number redirect loops removed
   */
  return [
    'number_nodes_checked' => NULL,
    'number_links_checked' => NULL,
    'links_updated' => NULL,
    'links_not_updated' => NULL,
    'links_deleted' => NULL,
    'errors_encountered' => NULL,
    '404s_found' => NULL,
    '404s_removed' => NULL,
    'redirect_loops_found' => NULL,
    'redirect_loops_removed' => NULL,
  ];
}

/**
 * Get the last-run's statistics.
 *
 * @param string $array_key_prefix
 *   An optional prefix for the returned array keys. This allows us to use this
 *   function to generate an array to use for variable replacement with t().
 *
 * @return array
 */
function _dennis_link_checker_last_run_statistics($array_key_prefix = '') {
  $last_run_statistics = variable_get(DENNIS_LINK_CHECKER_VARIABLE_LAST_RUN_STATISTICS, _dennis_link_checker_last_run_statistics_default());

  // Optionally prefix the array.
  if (!empty($array_key_prefix)) {
    foreach ($last_run_statistics as $key => $value) {
      $last_run_statistics[$array_key_prefix . $key] = $value;
      unset($last_run_statistics[$key]);
    }
  }

  // Remove the "previous_run" array if $array_key_prefix is "@".
  if ($array_key_prefix == '@') {
    unset($last_run_statistics['@previous_run']);
  }

  // Implement hook_dennis_link_checker_last_run_statistics_alter().
  drupal_alter('dennis_link_checker_last_run_statistics', $last_run_statistics);

  return $last_run_statistics;
}

/**
 * Get the default list of fields to check for broken links.
 *
 * @return string
 *   A comma-separated list of field names to check in each node.
 */
function _dennis_link_checker_fields_to_check_default_field_names() {
  $fields = ['body'];

  // Implement hook_dennis_link_checker_fields_to_check_alter().
  drupal_alter('dennis_link_checker_fields_to_check', $fields);

  return implode(',', $fields);
}

/**
 * Get an array of cron interval options.
 *
 * @return array
 */
function _dennis_link_checker_cron_interval_options($option_value_to_get_label_for = NULL) {
  $options = [
    DENNIS_LINK_CHECKER_CRON_01HRS => t(DENNIS_LINK_CHECKER_CRON_01HRS_LABEL),
    DENNIS_LINK_CHECKER_CRON_02HRS => t(DENNIS_LINK_CHECKER_CRON_02HRS_LABEL),
    DENNIS_LINK_CHECKER_CRON_06HRS => t(DENNIS_LINK_CHECKER_CRON_06HRS_LABEL),
    DENNIS_LINK_CHECKER_CRON_12HRS => t(DENNIS_LINK_CHECKER_CRON_12HRS_LABEL),
    DENNIS_LINK_CHECKER_CRON_24HRS => t(DENNIS_LINK_CHECKER_CRON_24HRS_LABEL),
  ];

  // Implement hook_dennis_link_checker_cron_interval_options_alter().
  drupal_alter('dennis_link_checker_cron_interval_options', $options);

  // If we have an option value to get a label for, just return that label.
  if (!empty($option_value_to_get_label_for)
    && array_key_exists($option_value_to_get_label_for, $options)) {
    return $options[$option_value_to_get_label_for];
  }

  return $options;
}

/**
 * Get an array of time limit options.
 *
 * @return array
 */
function _dennis_link_checker_time_limit_options($option_value_to_get_label_for = NULL) {
  $options = [
    DENNIS_LINK_CHECKER_TIME_LIMIT_01MINS => t(DENNIS_LINK_CHECKER_TIME_LIMIT_01MINS_LABEL),
    DENNIS_LINK_CHECKER_TIME_LIMIT_02MINS => t(DENNIS_LINK_CHECKER_TIME_LIMIT_02MINS_LABEL),
    DENNIS_LINK_CHECKER_TIME_LIMIT_04MINS => t(DENNIS_LINK_CHECKER_TIME_LIMIT_04MINS_LABEL),
    DENNIS_LINK_CHECKER_TIME_LIMIT_08MINS => t(DENNIS_LINK_CHECKER_TIME_LIMIT_08MINS_LABEL),
  ];

  // Implement hook_dennis_link_checker_time_limit_options_alter().
  drupal_alter('dennis_link_checker_time_limit_options', $options);

  // If we have an option value to get a label for, just return that label.
  if (!empty($option_value_to_get_label_for)
    && array_key_exists($option_value_to_get_label_for, $options)) {
    return $options[$option_value_to_get_label_for];
  }

  return $options;
}

/**
 * Get an array of check frequency options.
 *
 * @return array
 */
function _dennis_link_checker_check_frequency_options($option_value_to_get_label_for = NULL) {
  $options = [
    DENNIS_LINK_CHECKER_CHECK_FREQUENCY_01DAYS => t(DENNIS_LINK_CHECKER_CHECK_FREQUENCY_01DAYS_LABEL),
    DENNIS_LINK_CHECKER_CHECK_FREQUENCY_02DAYS => t(DENNIS_LINK_CHECKER_CHECK_FREQUENCY_02DAYS_LABEL),
    DENNIS_LINK_CHECKER_CHECK_FREQUENCY_04DAYS => t(DENNIS_LINK_CHECKER_CHECK_FREQUENCY_04DAYS_LABEL),
    DENNIS_LINK_CHECKER_CHECK_FREQUENCY_07DAYS => t(DENNIS_LINK_CHECKER_CHECK_FREQUENCY_07DAYS_LABEL),
    DENNIS_LINK_CHECKER_CHECK_FREQUENCY_14DAYS => t(DENNIS_LINK_CHECKER_CHECK_FREQUENCY_14DAYS_LABEL),
  ];

  // Implement hook_dennis_link_checker_check_frequency_options_alter().
  drupal_alter('dennis_link_checker_check_frequency_options', $options);

  // If we have an option value to get a label for, just return that label.
  if (!empty($option_value_to_get_label_for)
    && array_key_exists($option_value_to_get_label_for, $options)) {
    return $options[$option_value_to_get_label_for];
  }

  return $options;
}
