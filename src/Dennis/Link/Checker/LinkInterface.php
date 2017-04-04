<?php
/**
 * @file LinkInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface LinkInterface
 * @package Dennis\Link\Checker
 */
interface LinkInterface {

  public function __construct($entity_type, $entity_id, $field, $src);

  /**
   * The number of redirects needed to get to the corrected url.
   *
   * @return integer
   */
  public function getNumberOfRedirects();

  /**
   * Set the number of redirects needed to get to the corrected url.
   *
   * @param $int
   * @return LinkInterface
   */
  public function setNumberOfRedirects($int);

  /**
   * Whether the link was corrected.
   *
   * @return boolean
   */
  public function corrected();

  /**
   * The src to check.
   *
   * @param $url
   * @return LinkInterface
   */
  public function setOriginalSrc($src);

  /**
   * Gets the src to check.
   *
   * @return string
   */
  public function originalSrc();

  /**
   * Set the corrected $src.
   *
   * @param $url
   * @return LinkInterface
   */
  public function setCorrectedSrc($src);

  /**
   * The corrected $src.
   *
   * @return string
   */
  public function correctedSrc();

  /**
   * The url for this link that was found by the corrector.
   *
   * @param $url
   * @return LinkInterface
   */
  public function setFoundUrl($url);

  /**
   * The httpd code.
   *
   * @param $code
   * @return LinkInterface
   */
  public function setHttpCode($code);

}
