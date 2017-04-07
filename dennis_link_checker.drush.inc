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
  );

  return $items;
}

/**
 * Callback for drush vehicles_update_taxonomies.
 */
function drush_dennis_link_checker() {
  drush_print('Starting drush dennis_link_checker: ' . date(DATE_RFC2822));
  dennis_link_checker_run();
  drush_print('Finished drush dennis_link_checker: ' . date(DATE_RFC2822));
}
