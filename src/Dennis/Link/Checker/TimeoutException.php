<?php
/**
 * @file
 * TimeoutException
 */
namespace Dennis\Link\Checker;

/**
 * Class TimeoutException
 * @package Dennis\Link\Checker
 */
class TimeoutException extends \Exception {

  /**
   * TimeoutException constructor.
   *
   * @param null $message
   * @param int $code
   * @param \Exception|NULL $previous
   */
  public function __construct($message = NULL, $code = 0, \Exception $previous = NULL) {
    parent::__construct($message, $code, $previous);
  }

}
