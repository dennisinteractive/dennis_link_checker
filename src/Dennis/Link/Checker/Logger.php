<?php
/**
 * @file
 * Logger
 */
namespace Dennis\Link\Checker;

/**
 * Class Logger
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

  /**
   * Interesting events
   *
   * Examples: User logs in, SQL logs.
   */
  const INFO = 200;

  /**
   * Uncommon events
   */
  const NOTICE = 250;

  /**
   * Exceptional occurrences that are not errors
   *
   * Examples: Use of deprecated APIs, poor use of an API,
   * undesirable things that are not necessarily wrong.
   */
  const WARNING = 300;

  /**
   * Runtime errors
   */
  const ERROR = 400;

  /**
   * Critical conditions
   *
   * Example: Application component unavailable, unexpected exception.
   */
  const CRITICAL = 500;

  /**
   * Action must be taken immediately
   *
   * Example: Entire website down, database unavailable, etc.
   * This should trigger the SMS alerts and wake you up.
   */
  const ALERT = 550;

  const VERBOSITY_NONE = 0;
  const VERBOSITY_LOW = 1;
  const VERBOSITY_HIGH = 2;
  const VERBOSITY_DEBUG = 3;


  /**
   * Urgent alert.
   */
  const EMERGENCY = 600;

  protected $verbose_level = self::VERBOSITY_LOW;

  /**
   * How much to output while logging.
   *
   * @param $level
   * @return $this
   */
  public function setVerbosity($level) {
    $this->verbose_level = (int) $level;

    return $this;
  }

  /**
   * Adds a log record.
   *
   * @param  int     $level   The logging level
   * @param  string  $message The log message
   * @param  array   $context The log context
   * @return LoggerInterface
   */
  public function addRecord($level, $message, $context = []) {
    if (drupal_is_cli()) {
      $function = 'drush_print';
    }
    else {
      $function = 'drupal_set_message';
    }

    // Send Watchdog log entries (which in turn end up in Papertrail and other
    // reporting services).
    watchdog(DENNIS_LINK_CHECKER_WATCHDOG_LABEL, $message, $context, $this->mapDebugLevelsToWatchdogLevels($level), DENNIS_LINK_CHECKER_ADMINISTRATION_PATH_ROOT);

    if ($this->verbose_level == self::VERBOSITY_DEBUG) {
      if ($level >= self::DEBUG) {
        $function($message);
        if (!empty($context)) {
          $function('<pre>' . print_r($context) . '</pre>');
        }
      }
    }
    elseif ($this->verbose_level == self::VERBOSITY_HIGH) {
      if ($level >= self::INFO) {
        $function($message);
        if (!empty($context)) {
          $function('<pre>' . print_r($context) . '</pre>');
        }
      }
    }
    elseif ($this->verbose_level == self::VERBOSITY_LOW) {
      if ($level >= self::WARNING) {
        drupal_is_cli() ? $function($message) : $function($message, 'warning');
        if (!empty($context)) {
          $function('<pre>' . print_r($context) . '</pre>');
        }
      }
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
   * @see $this->addRecord()
   *
   * @param int|null $debug_level
   *   The specific debug level to get - optional.
   *
   * @return array|int
   *   Either the Watchdog debug level constant value, e.g. 1, 2, ... 7, or
   *   the entire mapping array.
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
   * @inheritDoc
   */
  public function emergency($message, array $context = array()) {
    $this->addRecord(self::EMERGENCY, (string) $message, $context);
  }

  /**
   * @inheritDoc
   */
  public function alert($message, array $context = array()) {
    $this->addRecord(self::ALERT, (string) $message, $context);
  }

  /**
   * @inheritDoc
   */
  public function critical($message, array $context = array()) {
    $this->addRecord(self::CRITICAL, (string) $message, $context);
  }

  /**
   * @inheritDoc
   */
  public function error($message, array $context = array()) {
    $this->addRecord(self::ERROR, (string) $message, $context);
  }

  /**
   * @inheritDoc
   */
  public function warning($message, array $context = array()) {
    $this->addRecord(self::WARNING, (string) $message, $context);
    // Special watchdog so the message can be automatically send to Slack.
    watchdog(DENNIS_LINK_CHECKER_WATCHDOG_LABEL, $message);
  }

  /**
   * @inheritDoc
   */
  public function notice($message, array $context = array()) {
    $this->addRecord(self::NOTICE, (string) $message, $context);
  }

  /**
   * @inheritDoc
   */
  public function info($message, array $context = array()) {
    $this->addRecord(self::INFO, (string) $message, $context);
  }

  /**
   * @inheritDoc
   */
  public function debug($message, array $context = array()) {
    $this->addRecord(self::DEBUG, (string) $message, $context);
  }

  /**
   * @inheritDoc
   */
  public function log($level, $message, array $context = array()) {
    $this->addRecord($level, (string) $message, $context);
  }

}
