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
  // Option
  $nid = drush_get_option('nids', '');
  $nids = !empty($nid) ? explode(',', $nid) : array();
  dennis_link_checker_run($nids);
  drush_print('Finished drush dennis_link_checker: ' . date(DATE_RFC2822));
}
