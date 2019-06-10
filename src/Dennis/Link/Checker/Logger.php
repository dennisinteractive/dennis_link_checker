<?php
/**
 * @file
 * Logger
 */

namespace Dennis\Link\Checker;

/**
 * Class Logger
 *
 * @package Dennis\Link\Checker
 */
class Logger implements LoggerInterface {

  // @TODO: none of the variabibbles passed in to the logger are being run
  // through t(), which isn't the end of the world, but also isn't great
  // practice, Karen...

  /**
   * Detailed debug information
   */
  const DEBUG = 100;
  const DEBUG_LABEL = 'Debug';

  /**
   * Interesting events
   *
   * Examples: User logs in, SQL logs.
   */
  const INFO = 200;
  const INFO_LABEL = 'Info';

  /**
   * Uncommon events
   */
  const NOTICE = 250;
  const NOTICE_LABEL = 'Notice';

  /**
   * Exceptional occurrences that are not errors
   *
   * Examples: Use of deprecated APIs, poor use of an API,
   * undesirable things that are not necessarily wrong.
   */
  const WARNING = 300;
  const WARNING_LABEL = 'Warning';

  /**
   * Runtime errors
   */
  const ERROR = 400;
  const ERROR_LABEL = 'Error';

  /**
   * Critical conditions
   *
   * Example: Application component unavailable, unexpected exception.
   */
  const CRITICAL = 500;
  const CRITICAL_LABEL = 'Critical';

  /**
   * Action must be taken immediately
   *
   * Example: Entire website down, database unavailable, etc.
   * This should trigger the SMS alerts and wake you up.
   */
  const ALERT = 550;
  const ALERT_LABEL = 'Alert';

  /**
   * Urgent alert.
   */
  const EMERGENCY = 600;
  const EMERGENCY_LABEL = 'Emergency';

  const VERBOSITY_NONE = 0;

  const VERBOSITY_LOW = 1;

  const VERBOSITY_HIGH = 2;

  const VERBOSITY_DEBUG = 3;

  protected $verbose_level = self::VERBOSITY_LOW;

  /**
   * How much to output while logging.
   *
   * @param $level
   *
   * @return $this
   */
  public function setVerbosity($level) {
    $this->verbose_level = (int) $level;

    return $this;
  }

  /**
   * Adds a log record.
   *
   * @param int $level The logging level
   * @param string $message The log message
   * @param array $variables The log context
   *
   * @return LoggerInterface
   */
  public function addRecord($level, $message, $variables = []) {
    // Create a version of $message with $variables added in.
    $message_parsed = t($this->getDebugLevelLabel($level) . ': ' . $message, $variables);

    // Send Watchdog log entries (which in turn end up in Papertrail and other
    // reporting services).
    watchdog(DENNIS_LINK_CHECKER_WATCHDOG_LABEL, $message, $variables, $this->mapDebugLevelsToWatchdogLevels($level), DENNIS_LINK_CHECKER_ADMINISTRATION_PATH_ROOT);

    // Create a flag we can use to track if we're going to show this message.
    $add_message = FALSE;

    if ($this->verbose_level == self::VERBOSITY_DEBUG) {
      if ($level >= self::DEBUG) {
        $add_message = TRUE;
      }
    }
    elseif ($this->verbose_level == self::VERBOSITY_HIGH) {
      if ($level >= self::INFO) {
        $add_message = TRUE;
      }
    }
    elseif ($this->verbose_level == self::VERBOSITY_LOW) {
      if ($level >= self::WARNING) {
        $add_message = TRUE;
      }
    }

    // Are we displaying this message?
    if ($add_message) {
      // Work out the message "type", dependent on where it's going and how
      // high the $level is.
      switch ($level) {
        case self::ALERT:
        case self::CRITICAL:
        case self::ERROR:
        $message_type = drupal_is_cli() ? 'failed' : 'error';
          break;

        case self::WARNING:
          $message_type = drupal_is_cli() ? 'failed' : 'warning';
          break;

        case self::NOTICE:
        case self::INFO:
        case self::DEBUG:
        default:
          $message_type = drupal_is_cli() ? 'ok' : 'status';
          break;
      }

      drupal_is_cli() ? drush_log($message_parsed, $message_type) : drupal_set_message($message_parsed, $message_type);
    }

    return $this;
  }

  /**
   * Given a dennis_link_checker log level, return the Watchdog log level.
   *
   * E.g. if you need to send a debug log entry to Watchdog, and you know the
   * dennis_link_checker debug level (100), this function will return the
   * value of WATCHDOG_DEBUG (7).
   *
   * @param int|null $debug_level
   *   The specific debug level to get - optional.
   *
   * @return array|int
   *   Either the Watchdog debug level constant value, e.g. 1, 2, ... 7, or
   *   the entire mapping array.
   *
   * @see $this->addRecord()
   */
  public function mapDebugLevelsToWatchdogLevels($debug_level = NULL) {
    $map = [
      self::ALERT => WATCHDOG_ALERT,
      self::CRITICAL => WATCHDOG_CRITICAL,
      self::ERROR => WATCHDOG_ERROR,
      self::WARNING => WATCHDOG_WARNING,
      self::NOTICE => WATCHDOG_NOTICE,
      self::INFO => WATCHDOG_INFO,
      self::DEBUG => WATCHDOG_DEBUG,
    ];

    // Have we been asked to map a dennis_link_checker log level to a
    // watchdog.module log level?
    if (!is_null($debug_level)) {
      // Note that we assume the array key $map[$debug_level] exists; we want
      // to fail noisily if it doesn't.
      return $map[$debug_level];
    }

    return $map;
  }

  /**
   * Given a dennis_link_checker log level, return its label.
   *
   * @param int|null $debug_level
   *   The specific debug level to get - optional.
   *
   * @return array|string
   *   Either the Watchdog debug level's label, or the entire mapping array.
   */
  public function getDebugLevelLabel($debug_level = NULL) {
    $map = [
      self::ALERT => t(self::ALERT_LABEL),
      self::CRITICAL => t(self::CRITICAL_LABEL),
      self::ERROR => t(self::ERROR_LABEL),
      self::WARNING => t(self::WARNING_LABEL),
      self::NOTICE => t(self::NOTICE_LABEL),
      self::INFO => t(self::INFO_LABEL),
      self::DEBUG => t(self::DEBUG_LABEL),
    ];

    // Have we been asked to map a dennis_link_checker log level to a label?
    if (!is_null($debug_level)) {
      // Note that we assume the array key $map[$debug_level] exists; we want
      // to fail noisily if it doesn't.
      return $map[$debug_level];
    }

    return $map;
  }

  /**
   * @inheritDoc
   */
  public function emergency($message, array $variables = []) {
    $this->addRecord(self::EMERGENCY, (string) $message, $variables);
  }

  /**
   * @inheritDoc
   */
  public function alert($message, array $variables = []) {
    $this->addRecord(self::ALERT, (string) $message, $variables);
  }

  /**
   * @inheritDoc
   */
  public function critical($message, array $variables = []) {
    $this->addRecord(self::CRITICAL, (string) $message, $variables);
  }

  /**
   * @inheritDoc
   */
  public function error($message, array $variables = []) {
    $this->addRecord(self::ERROR, (string) $message, $variables);
  }

  /**
   * @inheritDoc
   */
  public function warning($message, array $variables = []) {
    $this->addRecord(self::WARNING, (string) $message, $variables);
  }

  /**
   * @inheritDoc
   */
  public function notice($message, array $variables = []) {
    $this->addRecord(self::NOTICE, (string) $message, $variables);
  }

  /**
   * @inheritDoc
   */
  public function info($message, array $variables = []) {
    $this->addRecord(self::INFO, (string) $message, $variables);
  }

  /**
   * @inheritDoc
   */
  public function debug($message, array $variables = []) {
    $this->addRecord(self::DEBUG, (string) $message, $variables);
  }

  /**
   * @inheritDoc
   */
  public function log($level, $message, array $variables = []) {
    $this->addRecord($level, (string) $message, $variables);
  }

}
