<?php

/**
 * @file
 * Defines constants for the dennis_link_checker module.
 */

/**
 * Time intervals between runs, in seconds.
 */
define('DENNIS_LINK_CHECKER_CRON_00HRS', 0);
define('DENNIS_LINK_CHECKER_CRON_00HRS_LABEL', 'No minimum time interval - always run (recommended for testing only)');
define('DENNIS_LINK_CHECKER_CRON_30MINS', 60 * 30);
define('DENNIS_LINK_CHECKER_CRON_30MINS_LABEL', '30 minutes');
define('DENNIS_LINK_CHECKER_CRON_01HRS', 60 * 60 * 1);
define('DENNIS_LINK_CHECKER_CRON_01HRS_LABEL', '1 hour');
define('DENNIS_LINK_CHECKER_CRON_02HRS', 60 * 60 * 2);
define('DENNIS_LINK_CHECKER_CRON_02HRS_LABEL', '2 hours');
define('DENNIS_LINK_CHECKER_CRON_06HRS', 60 * 60 * 6);
define('DENNIS_LINK_CHECKER_CRON_06HRS_LABEL', '6 hours');
define('DENNIS_LINK_CHECKER_CRON_12HRS', 60 * 60 * 12);
define('DENNIS_LINK_CHECKER_CRON_12HRS_LABEL', '12 hours');
define('DENNIS_LINK_CHECKER_CRON_24HRS', 60 * 60 * 24);
define('DENNIS_LINK_CHECKER_CRON_24HRS_LABEL', '1 day');

/**
 * Maximum allowable time for runs, in seconds.
 */
define('DENNIS_LINK_CHECKER_TIME_LIMIT_01MINS', 60 * 1);
define('DENNIS_LINK_CHECKER_TIME_LIMIT_01MINS_LABEL', '1 minute');
define('DENNIS_LINK_CHECKER_TIME_LIMIT_02MINS', 60 * 2);
define('DENNIS_LINK_CHECKER_TIME_LIMIT_02MINS_LABEL', '2 minutes');
define('DENNIS_LINK_CHECKER_TIME_LIMIT_04MINS', 60 * 4);
define('DENNIS_LINK_CHECKER_TIME_LIMIT_04MINS_LABEL', '4 minutes');
define('DENNIS_LINK_CHECKER_TIME_LIMIT_08MINS', 60 * 8);
define('DENNIS_LINK_CHECKER_TIME_LIMIT_08MINS_LABEL', '8 minutes');
define('DENNIS_LINK_CHECKER_TIME_LIMIT_16MINS', 60 * 16);
define('DENNIS_LINK_CHECKER_TIME_LIMIT_16MINS_LABEL', '16 minutes');
define('DENNIS_LINK_CHECKER_TIME_LIMIT_32MINS', 60 * 32);
define('DENNIS_LINK_CHECKER_TIME_LIMIT_32MINS_LABEL', '32 minutes');

/**
 * How frequently should links be checked?
 */
define('DENNIS_LINK_CHECKER_CHECK_FREQUENCY_01DAYS', 1);
define('DENNIS_LINK_CHECKER_CHECK_FREQUENCY_01DAYS_LABEL', '1 day');
define('DENNIS_LINK_CHECKER_CHECK_FREQUENCY_02DAYS', 2);
define('DENNIS_LINK_CHECKER_CHECK_FREQUENCY_02DAYS_LABEL', '2 days');
define('DENNIS_LINK_CHECKER_CHECK_FREQUENCY_04DAYS', 4);
define('DENNIS_LINK_CHECKER_CHECK_FREQUENCY_04DAYS_LABEL', '4 days');
define('DENNIS_LINK_CHECKER_CHECK_FREQUENCY_07DAYS', 7);
define('DENNIS_LINK_CHECKER_CHECK_FREQUENCY_07DAYS_LABEL', '1 week');
define('DENNIS_LINK_CHECKER_CHECK_FREQUENCY_14DAYS', 14);
define('DENNIS_LINK_CHECKER_CHECK_FREQUENCY_14DAYS_LABEL', '2 weeks');

/**
 * The module name; also used as a variable name prefix.
 */
define('DENNIS_LINK_CHECKER_MODULE_NAME', 'dennis_link_checker');

/**
 * The Watchdog label: when messages with this label are sent to the Dennis
 * logging service, a message is generated in a special Slack channel which
 * has been set up to monitor for Link Checker updates.
 */
define('DENNIS_LINK_CHECKER_WATCHDOG_LABEL', DENNIS_LINK_CHECKER_MODULE_NAME . '_seo');

/**
 * {variable} table names and default values.
 */

// Run interval, seconds.
define('DENNIS_LINK_CHECKER_VARIABLE_RUN_INTERVAL', DENNIS_LINK_CHECKER_MODULE_NAME . '_run_interval');

// Default run interval.
define('DENNIS_LINK_CHECKER_RUN_INTERVAL_DEFAULT', DENNIS_LINK_CHECKER_CRON_24HRS);

// Time limit - don't allow runs to exceed this number of seconds.
define('DENNIS_LINK_CHECKER_VARIABLE_TIME_LIMIT', DENNIS_LINK_CHECKER_MODULE_NAME . '_time_limit');

// Default time limit, seconds.
define('DENNIS_LINK_CHECKER_TIME_LIMIT_DEFAULT', DENNIS_LINK_CHECKER_TIME_LIMIT_04MINS);

// Last started time, Unix timestamp.
define('DENNIS_LINK_CHECKER_VARIABLE_LAST_STARTED_TIME', DENNIS_LINK_CHECKER_MODULE_NAME . '_last_started_time');

// Default (initial) last started time.
define('DENNIS_LINK_CHECKER_LAST_STARTED_TIME_DEFAULT', 0);

// Checker is running, boolean.
define('DENNIS_LINK_CHECKER_VARIABLE_IS_RUNNING', DENNIS_LINK_CHECKER_MODULE_NAME . '_is_running');

// Default (initial) is-running boolean.
define('DENNIS_LINK_CHECKER_IS_RUNNING_DEFAULT', FALSE);

// Last completed time, Unix timestamp.
define('DENNIS_LINK_CHECKER_VARIABLE_LAST_COMPLETED_TIME', DENNIS_LINK_CHECKER_MODULE_NAME . '_last_completed_time');

// Default (initial) last completed time.
define('DENNIS_LINK_CHECKER_LAST_COMPLETED_TIME_DEFAULT', 0);

// Last skipped time, Unix timestamp.
define('DENNIS_LINK_CHECKER_VARIABLE_LAST_SKIPPED_TIME', DENNIS_LINK_CHECKER_MODULE_NAME . '_last_skipped_time');

// Default (initial) last skipped time.
define('DENNIS_LINK_CHECKER_LAST_SKIPPED_TIME_DEFAULT', 0);

// Skip counter - increment each time we want to run the checker but it's still running.
define('DENNIS_LINK_CHECKER_VARIABLE_NUMBER_RUNS_SKIPPED', DENNIS_LINK_CHECKER_MODULE_NAME . '_number_runs_skipped');

// Default (initial) skip counter.
define('DENNIS_LINK_CHECKER_NUMBER_RUNS_SKIPPED_DEFAULT', 0);

// Force run after how many skips - reset the "is running" boolean and force a run after this many skipped runs.
define('DENNIS_LINK_CHECKER_VARIABLE_FORCE_RUN_AFTER_X_SKIPS', DENNIS_LINK_CHECKER_MODULE_NAME . '_force_run_after_x_skips');

// Force run after how many skips, default.
define('DENNIS_LINK_CHECKER_FORCE_RUN_AFTER_X_SKIPS_DEFAULT', 4);

// Don't check links more frequently than X days.
define('DENNIS_LINK_CHECKER_VARIABLE_CHECK_FREQUENCY', DENNIS_LINK_CHECKER_MODULE_NAME . '_check_frequency');

// Don't check links more frequently than X days, default.
define('DENNIS_LINK_CHECKER_CHECK_FREQUENCY_DEFAULT', 7);

// Last run statistics.
define('DENNIS_LINK_CHECKER_VARIABLE_LAST_RUN_STATISTICS', DENNIS_LINK_CHECKER_MODULE_NAME . '_last_run_statistics');

// Force run after how many skips, default.
define('DENNIS_LINK_CHECKER_LAST_RUN_STATISTICS_DEFAULT', NULL);

// What fields should be checked by the link checker?
define('DENNIS_LINK_CHECKER_VARIABLE_FIELDS_TO_CHECK', DENNIS_LINK_CHECKER_MODULE_NAME . '_fields');

/**
 * Permissions.
 */

// Permission to view the Link Checker overview and statistics page.
define('DENNIS_LINK_CHECKER_PERMISSION_ACCESS_LINK_CHECKER_OVERVIEW', 'access dennis link checker overview page');
define('DENNIS_LINK_CHECKER_PERMISSION_ACCESS_LINK_CHECKER_OVERVIEW_LABEL', 'Access the Dennis Link Checker overview page.');

// Permission to run the link checker, e.g. from the overview page.
define('DENNIS_LINK_CHECKER_PERMISSION_RUN_LINK_CHECKER', 'run dennis link checker');
define('DENNIS_LINK_CHECKER_PERMISSION_RUN_LINK_CHECKER_LABEL', 'Run Dennis Link Checker.');

// Permission to configure the link checker.
define('DENNIS_LINK_CHECKER_PERMISSION_CONFIGURE_LINK_CHECKER', 'configure dennis link checker');
define('DENNIS_LINK_CHECKER_PERMISSION_CONFIGURE_LINK_CHECKER_LABEL', 'Configure Dennis Link Checker.');

/**
 * Administration section paths.
 */
define('DENNIS_LINK_CHECKER_ADMINISTRATION_PATH_ROOT', 'admin/config/dennis-link-checker');
