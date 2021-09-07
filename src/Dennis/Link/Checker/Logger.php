<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class Logger.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Logger implements LoggerInterface {


  /**
   * Logger channel factory interface.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Logger constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger channel factory interface.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerFactory) {
    $this->logger = $loggerFactory;
  }

  /**
   * Detailed debug information.
   */
  const DEBUG = 100;

  /**
   * Interesting events.
   *
   * Examples: User logs in, SQL logs.
   */
  const INFO = 200;

  /**
   * Uncommon events.
   */
  const NOTICE = 250;

  /**
   * Exceptional occurrences that are not errors.
   *
   * Examples: Use of deprecated APIs, poor use of an API,
   * undesirable things that are not necessarily wrong.
   */
  const WARNING = 300;

  /**
   * Runtime errors.
   */
  const ERROR = 400;

  /**
   * Critical conditions.
   *
   * Example: Application component unavailable, unexpected exception.
   */
  const CRITICAL = 500;

  /**
   * Action must be taken immediately.
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

  /**
   * Verbose level.
   *
   * @var int
   */
  protected $verboseLevel = self::VERBOSITY_LOW;

  /**
   * How much to output while logging.
   *
   * @param string $level
   *   Verbosity Level.
   *
   * @return $this
   */
  public function setVerbosity($level) {
    $this->verboseLevel = (int) $level;

    return $this;
  }

  /**
   * Adds a log record.
   *
   * @param int $level
   *   The logging level.
   * @param string $message
   *   The log message.
   * @param array $context
   *   The log context.
   *
   * @return LoggerInterface
   *   Returns the logger interface.
   */
  public function addRecord($level, $message, array $context = []) {

    if ($this->verboseLevel == self::VERBOSITY_DEBUG) {
      if ($level >= self::DEBUG) {
        print $message . "\n";
        if (!empty($context)) {
          print_r($context);
        }
      }
    }
    elseif ($this->verboseLevel == self::VERBOSITY_HIGH) {
      if ($level >= self::INFO) {
        print $message . "\n";
        if (!empty($context)) {
          print_r($context);
        }
      }
    }
    elseif ($this->verboseLevel == self::VERBOSITY_LOW) {
      if ($level >= self::WARNING) {
        print $message . "\n";
        if (!empty($context)) {
          print_r($context);
        }
      }
    }

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function emergency($message, array $context = []) {
    $this->addRecord(self::EMERGENCY, (string) $message, $context);
    $this->setDrupalLog()->emergency($message);
  }

  /**
   * {@inheritDoc}
   */
  public function alert($message, array $context = []) {
    $this->addRecord(self::ALERT, (string) $message, $context);
    $this->setDrupalLog()->critical($message);
  }

  /**
   * {@inheritDoc}
   */
  public function critical($message, array $context = []) {
    $this->addRecord(self::CRITICAL, (string) $message, $context);
  }

  /**
   * {@inheritDoc}
   */
  public function error($message, array $context = []) {
    $this->addRecord(self::ERROR, (string) $message, $context);
    $this->setDrupalLog()->error($message);
  }

  /**
   * {@inheritDoc}
   */
  public function warning($message, array $context = []) {
    $this->addRecord(self::WARNING, (string) $message, $context);
    // Special watchdog so the message can be automatically send to Slack.
    $this->setDrupalLog()->warning($message);
  }

  /**
   * {@inheritDoc}
   */
  public function notice($message, array $context = []) {
    $this->addRecord(self::NOTICE, (string) $message, $context);
    $this->setDrupalLog()->notice($message);
  }

  /**
   * {@inheritDoc}
   */
  public function info($message, array $context = []) {
    $this->addRecord(self::INFO, (string) $message, $context);
    $this->setDrupalLog()->info($message);
  }

  /**
   * {@inheritDoc}
   */
  public function debug($message, array $context = []) {
    $this->addRecord(self::DEBUG, (string) $message, $context);
    $this->setDrupalLog()->debug($message);
  }

  /**
   * {@inheritDoc}
   */
  public function log($level, $message, array $context = []) {
    $this->addRecord($level, (string) $message, $context);
  }

  /**
   * Set drupal log.
   *
   * @return \Psr\Log\LoggerInterface
   *   Returns Logge interface.
   */
  protected function setDrupalLog() {
    return $this->logger->get('dennis_link_checker');
  }

}
