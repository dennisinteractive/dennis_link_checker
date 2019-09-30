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
function dennis_asset_checker_drush_command() {
  $items = array();

  $items['dennis_asset_checker'] = array(
    'description' => "Checks & fixes assets ",
    'aliases' => array('dac'),
    'options' => array(
      'nids' => 'The comma-separated list of nids to check',
    )
  );

  return $items;
}

/**
 * Callback for dennis_asset_checker_drush_command().
 */
function drush_dennis_asset_checker() {
  drush_print('Starting drush dennis_asset_checker: ' . date(DATE_RFC2822));
  // Option
  $nid = drush_get_option('nids', '');
  $nids = !empty($nid) ? explode(',', $nid) : array();
  dennis_asset_checker_run($nids);
  drush_print('Finished drush dennis_asset_checker: ' . date(DATE_RFC2822));
}
