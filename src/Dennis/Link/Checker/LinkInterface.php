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
   * The type of the entity.
   * @return string
   */
  public function entityType();

  /**
   * The entity id.
   * @return integer
   */
  public function entityId();

  /**
   * The field the link is in.
   * @return string
   */
  public function entityField();

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
   * Marke the link has having too many redirects.
   * @return LinkInterface
   */
  public function setTooManyRedirects();

  /**
   * @return boolean
   */
  public function hasTooManyRedirects();

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

  public function getFoundUrl();

  /**
   * The httpd code.
   *
   * @param $code
   * @return LinkInterface
   */
  public function setHttpCode($code);

  /**
   * The checker error.
   *
   * @param $code
   * @param $msg
   * @return LinkInterface
   */
  public function setError($code, $msg);

  /**
   * @return array
   */
  public function getError();

}
