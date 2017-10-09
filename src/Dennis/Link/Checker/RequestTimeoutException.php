<?php
/**
 * @file
 * RequestTimeoutException
 */
namespace Dennis\Link\Checker;

/**
 * Class RequestTimeoutException
 * @package Dennis\Link\Checker
 */
class RequestTimeoutException extends \Exception {

  /**
   * RequestTimeoutException constructor.
   *
   * @param null $message
   * @param int $code
   * @param \Exception|NULL $previous
   */
  public function __construct($message = NULL, $code = 0, \Exception $previous = NULL) {
    parent::__construct($message, $code, $previous);
  }

}
