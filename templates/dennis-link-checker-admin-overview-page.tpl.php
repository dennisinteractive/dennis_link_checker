<?php

/**
 * @file
 * dennis-link-checker-admin-overview-page.tpl.php
 */

?>

<?php if (!empty($run_interval)): ?>
  <p><?php print $run_interval; ?></p>
<?php endif; ?>

<?php if (!empty($time_limit)): ?>
  <p><?php print $time_limit; ?></p>
<?php endif; ?>

<?php if (!empty($last_run_started)): ?>
  <p><?php print $last_run_started; ?></p>
<?php endif; ?>

<?php if (!empty($last_run_completed)): ?>
  <p><?php print $last_run_completed; ?></p>
<?php endif; ?>

<?php if (!empty($last_run_skipped)): ?>
  <p><?php print $last_run_skipped; ?></p>
<?php endif; ?>

<?php if (!empty($running_now)): ?>
  <p><?php print $running_now; ?></p>
<?php endif; ?>

<?php if (!empty($force_after_skips)): ?>
  <p><?php print $force_after_skips; ?></p>
<?php endif; ?>

<?php if (!empty($fields_to_check_for_links)): ?>
  <p><?php print $fields_to_check_for_links; ?></p>
<?php endif; ?>

<?php if (!empty($last_run_stats)): ?>
  <br />
  <hr />
  <br />

  <h3><?php print t('Last link checker run statistics'); ?></h3>
  <div class="last-run-stats"><?php print $last_run_stats; ?></div>
<?php endif; ?>

<?php if (!empty($run_link_checker_form)): ?>
  <div class="run-form"><?php print $run_link_checker_form; ?></div>
<?php endif; ?>


