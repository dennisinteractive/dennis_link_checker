<?php
/**
 * @file
 *   Drush integration.
 */

/**
 * Implements hook_drush_command().
 *
 * @return array
 *  An associative array describing the command(s).
 */
function dennis_link_checker_drush_command() {
  $items = array();

  $items['dennis_link_checker'] = array(
    'description' => "Checks & fixes links that redirect.",
    'aliases' => array('dlc'),
    'options' => array(
      'nids' => 'The comma-separated list of nids to check',
    )
  );

  return $items;
}

/**
 * Callback for dennis_link_checker_drush_command().
 */
function drush_dennis_link_checker() {
  drush_print('Starting drush dennis_link_checker: ' . date(DATE_RFC2822));

  // Has the command been run with the nids option, e.g. 'nids=123,456,789'?
  $nid = drush_get_option('nids', '');
  $nids = !empty($nid) ? explode(',', $nid) : array();

  // Has the command been run with the timeout option, e.g. 'timeout=999'
  // (seconds).
  $timeout_raw = drush_get_option('timeout', '');

  if (!empty($timeout) && is_numeric($timeout)) {
    $timeout = $timeout_raw;
  }
  else {
    $timeout = DENNIS_LINK_CHECKER_TIME_LIMIT_32MINS;
  }

  // Run it!
  dennis_link_checker_run($nids, $timeout);

  drush_print('Finished drush dennis_link_checker: ' . date(DATE_RFC2822));
}
