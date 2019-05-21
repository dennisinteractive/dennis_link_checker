<?php

/**
 * @file
 * dennis_link_checker.admin.inc
 *
 * Administration functions for the Dennis Link Checker of Awesomeness.
 */

/**
 * Build the output for the administration overview page.
 */
function dennis_link_checker_callback_overview_page() {
  // Check if the database table is available.
  if (!db_table_exists('dennis_link_checker_checked_nodes')) {
    drupal_set_message(t('Database schema not installed for dennis_link_checker - please run database updates.'), 'error');
  }

  $variables = [];

  // Assemble page content.

  // Run interval.
  $variables['run_interval'] = t('Run interval: <em>@value</em>', [
    '@value' => _dennis_link_checker_cron_interval_options(variable_get(DENNIS_LINK_CHECKER_VARIABLE_RUN_INTERVAL, DENNIS_LINK_CHECKER_RUN_INTERVAL_DEFAULT)),
  ]);

  // Time limit.
  $variables['time_limit'] = t('Time limit for each run: <em>@value</em>', [
    '@value' => _dennis_link_checker_time_limit_options(variable_get(DENNIS_LINK_CHECKER_VARIABLE_TIME_LIMIT, DENNIS_LINK_CHECKER_TIME_LIMIT_DEFAULT)),
  ]);

  // Last run started.
  $variables['last_run_started'] = t('Last run start time: <em>@value</em>', [
    '@value' => format_date(variable_get(DENNIS_LINK_CHECKER_VARIABLE_LAST_STARTED_TIME, DENNIS_LINK_CHECKER_LAST_STARTED_TIME_DEFAULT), 'medium'),
  ]);

  // Last successful run completed.
  $variables['last_run_completed'] = t('Last run completed successfully: <em>@value</em>', [
    '@value' => format_date(variable_get(DENNIS_LINK_CHECKER_VARIABLE_LAST_COMPLETED_TIME, DENNIS_LINK_CHECKER_LAST_COMPLETED_TIME_DEFAULT), 'medium'),
  ]);

  // Last run skipped.
  $variables['last_run_skipped'] = t('Last run skipped (e.g. because a run is already underway): <em>@value</em>', [
    '@value' => format_date(variable_get(DENNIS_LINK_CHECKER_VARIABLE_LAST_SKIPPED_TIME, DENNIS_LINK_CHECKER_LAST_SKIPPED_TIME_DEFAULT), 'medium'),
  ]);

  // Is running now?
  $variables['running_now'] = t('Current status: <em>@value</em>', [
    '@value' => variable_get(DENNIS_LINK_CHECKER_VARIABLE_IS_RUNNING, DENNIS_LINK_CHECKER_IS_RUNNING_DEFAULT) ? t('Running') : t('Not running'),
  ]);

  // Force run after how many skips?
  $variables['force_after_skips'] = t('Force link checker to run after skipping how many runs? <em>@value</em>', [
    '@value' => variable_get(DENNIS_LINK_CHECKER_VARIABLE_FORCE_RUN_AFTER_X_SKIPS, DENNIS_LINK_CHECKER_FORCE_RUN_AFTER_X_SKIPS_DEFAULT),
  ]);

  // Fields to check.
  $variables['fields_to_check_for_links'] = t('Check the following fields for links: <em>@value</em>', [
    '@value' => implode(', ', explode(',', variable_get(DENNIS_LINK_CHECKER_VARIABLE_FIELDS_TO_CHECK, _dennis_link_checker_fields_to_check_default_field_names()))),
  ]);

  // Hmm... Can't use the Queue class without adding a Use statement at the
  // top of the file. Need to check with the D8 devs how to do this correctly...

//  // Queue size.
//  $queue = new Queue('dennis_link_checker');
//  $variables['queue_size'] = t('Number of fields queued for link checking: <em>@value</em>', [
//    '@value' => $queue->count(),
//  ]);

  // Last run statistics.
  $last_run_stats_variables = _dennis_link_checker_last_run_statistics('@');

  $last_run_stats_rows = [];
  $last_run_stats_rows[] = t('Number of links checked: @number_checked', $last_run_stats_variables);
  $last_run_stats_rows[] = t('Redirects updated: @links_updated', $last_run_stats_variables);
  $last_run_stats_rows[] = t('Errors encountered: @errors_encountered', $last_run_stats_variables);
  $last_run_stats_rows[] = t('404 "page not found" errors encountered: @404s_found', $last_run_stats_variables);
  $last_run_stats_rows[] = t('404 "page not found" errors fixed: @404s_removed', $last_run_stats_variables);
  $last_run_stats_rows[] = t('Redirect loops found: @redirect_loops_found', $last_run_stats_variables);
  $last_run_stats_rows[] = t('Redirect loops removed: @redirect_loops_removed', $last_run_stats_variables);

  $variables['last_run_stats'] = theme('item_list', [
    'items' => $last_run_stats_rows,
    'type' => 'ul',
  ]);

  $variables['run_link_checker_form'] = NULL;
  if (user_access(DENNIS_LINK_CHECKER_PERMISSION_RUN_LINK_CHECKER)) {
    $run_link_checker_form = drupal_get_form('dennis_link_checker_overview_page_run_form');
    $variables['run_link_checker_form'] = drupal_render($run_link_checker_form);
  }

  return theme(DENNIS_LINK_CHECKER_MODULE_NAME . '_overview_page', $variables);
}

/**
 * Builds and returns the module's configuration form.
 *
 * @return mixed
 */
function dennis_link_checker_callback_config_form() {
  $form = [];

  $form[DENNIS_LINK_CHECKER_VARIABLE_CHECK_FREQUENCY] = [
    '#title' => t("How long should we wait between checking each piece of content's links?"),
    '#type' => 'select',
    '#options' => _dennis_link_checker_check_frequency_options(),
    '#default_value' => variable_get(DENNIS_LINK_CHECKER_VARIABLE_CHECK_FREQUENCY, DENNIS_LINK_CHECKER_CHECK_FREQUENCY_DEFAULT),
  ];

  $form[DENNIS_LINK_CHECKER_VARIABLE_TIME_LIMIT] = [
    '#title' => t('Time limit for each run:'),
    '#type' => 'select',
    '#options' => _dennis_link_checker_time_limit_options(),
    '#default_value' => variable_get(DENNIS_LINK_CHECKER_VARIABLE_TIME_LIMIT, DENNIS_LINK_CHECKER_TIME_LIMIT_DEFAULT),
  ];

  $form[DENNIS_LINK_CHECKER_VARIABLE_RUN_INTERVAL] = [
    '#title' => t('Run link checker how frequently?'),
    '#type' => 'select',
    '#options' => _dennis_link_checker_cron_interval_options(),
    '#default_value' => variable_get(DENNIS_LINK_CHECKER_VARIABLE_RUN_INTERVAL, DENNIS_LINK_CHECKER_RUN_INTERVAL_DEFAULT),
  ];

  $form[DENNIS_LINK_CHECKER_VARIABLE_FORCE_RUN_AFTER_X_SKIPS] = [
    '#title' => t('Force the checker to run after how many failed attempts?'),
    "#description" => t('Normally, the link checker will not run if the previous run hasn\'t completed to avoid having more than one link check task running concurrently. However, if the process stalls or is aborted for any reason, the link checker will always fail. In this case, we will force the link checker to run after a certain number of failed attempts - by default, this is @default attemps.', [
      '@default' => DENNIS_LINK_CHECKER_FORCE_RUN_AFTER_X_SKIPS_DEFAULT,
    ]),
    '#type' => 'select',
    '#options' => array_combine(_dennis_link_checker_get_skip_options(), _dennis_link_checker_get_skip_options()),
    '#default_value' => variable_get(DENNIS_LINK_CHECKER_VARIABLE_FORCE_RUN_AFTER_X_SKIPS, DENNIS_LINK_CHECKER_FORCE_RUN_AFTER_X_SKIPS_DEFAULT),
  ];

  $form[DENNIS_LINK_CHECKER_VARIABLE_FIELDS_TO_CHECK] = [
    '#title' => t('Which fields should be checked for links?'),
    '#description' => t('Enter a comma-separated list of fields which should be scanned for links. Field names should be lower-case with underscores, for example <em>body,field_monkey,field_banana,field_turkeymango</em>.'),
    '#type' => 'textarea',
    '#default_value' => variable_get(DENNIS_LINK_CHECKER_VARIABLE_FIELDS_TO_CHECK, _dennis_link_checker_fields_to_check_default_field_names()),
  ];

  return system_settings_form($form);
}

/**
 * Get the form which allows users with permission to run the link checker.
 *
 * @param array $form
 * @param array $form_state
 *
 * @return array
 *   The form array.
 */
function dennis_link_checker_overview_page_run_form($form, &$form_state) {
  // Assemble the form.
  $form['title'] = [
    '#value' => '<h3>' . t('Run the link checker') . '</h3>'
      . '<p>' . t('You can run the link checker by clicking the "Run" button, below. Optionally, you can enter one or more node IDs as a comma-separated list if you only want to check specific nodes (pages).') . '</p>',
  ];

  $form['node_ids_to_check'] = [
    '#title' => t('Specific node IDs to check'),
    '#description' => t('Optional: enter one or more node IDs separated by commas to check only those nodes. For example: <em>123,456,789,101010</em>'),
    '#type' => 'textfield',
    '#default_value' => !empty($_SESSION[DENNIS_LINK_CHECKER_MODULE_NAME]['node_ids_to_check']) ? check_plain($_SESSION[DENNIS_LINK_CHECKER_MODULE_NAME]['node_ids_to_check']) : '',
  ];

  $form['submit'] = [
    '#value' => t('Run'),
    '#type' => 'submit',
  ];

  return $form;
}

/**
 * Validate the dennis_link_checker_overview_page_run_form form.
 *
 * @param array $form
 * @param array $form_state
 */
function dennis_link_checker_overview_page_run_form_validate($form, &$form_state) {
  // If we have node IDs, clean them up and make sure they're all numeric.
  if (!empty($form_state['values']['node_ids_to_check'])) {
    $node_ids_to_check = trim($form_state['values']['node_ids_to_check']);
    $node_ids_to_check_array = explode(',', $node_ids_to_check);

    if (!empty($node_ids_to_check_array)) {
      foreach ($node_ids_to_check_array as $key => &$node_id_to_check) {
        $node_id_to_check = trim($node_id_to_check);

        // If it ends up being blank, remove and move on.
        if (empty($node_id_to_check)) {
          unset($node_ids_to_check_array[$key]);
          continue;
        }

        if (!is_numeric($node_id_to_check)) {
          form_set_error('node_ids_to_check', t('Please only enter numeric values and commas in this field.'));
        }
      }
    }

    // Update the $form_state['values']['node_ids_to_check'] field.
    $form_state['values']['node_ids_to_check'] = implode(',', $node_ids_to_check_array);
  }
}

/**
 * Run the dennis_link_checker_overview_page_run_form form form.
 *
 * @param array $form
 * @param array $form_state
 */
function dennis_link_checker_overview_page_run_form_submit($form, &$form_state) {
  // Save the node IDs for the next time we load the form.
  if (empty($_SESSION[DENNIS_LINK_CHECKER_MODULE_NAME])) {
    $_SESSION[DENNIS_LINK_CHECKER_MODULE_NAME] = [];
  }

  $node_ids_to_check = [];
  if (!empty($form_state['values']['node_ids_to_check'])) {
    $node_ids_to_check = explode(',', $form_state['values']['node_ids_to_check']);
  }

  $_SESSION[DENNIS_LINK_CHECKER_MODULE_NAME]['node_ids_to_check'] = $form_state['values']['node_ids_to_check'];

  // Run tings.
  $link_checker_processor = dennis_link_checker_run($node_ids_to_check);

  // Set a success or error drupal_message().
  drupal_set_message(t('Link Checker run completed. Please review the "Last Run Statistics" on the !link_checker_overview_page.', [
    '!link_checker_overview_page' => l(t('link checker overview page'), DENNIS_LINK_CHECKER_ADMINISTRATION_PATH_ROOT),
  ]));
}
