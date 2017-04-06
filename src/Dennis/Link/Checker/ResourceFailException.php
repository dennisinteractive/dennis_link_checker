<?php
/**
 * @file
 * ResourceFailException
 */
namespace Dennis\Link\Checker;

/**
 * Class ResourceFailException
 * @package Buyacar\Site\Search\Filter
 */
class ResourceFailException extends \OutOfRangeException {

  /**
   * ResourceFailException constructor.
   *
   * @param null $message
   * @param int $code
   * @param \Exception|NULL $previous
   */
  public function __construct($message = NULL, $code = 0, \Exception $previous = NULL) {
    parent::__construct($message, $code, $previous);
  }

}
