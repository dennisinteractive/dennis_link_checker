<?php
/**
 * @file
 * LinkInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface LinkInterface
 * @package Dennis\Link\Checker
 */
interface LinkInterface {
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
   * The href to check.
   *
   * @param $href
   * @return LinkInterface
   */
  public function setOriginalHref($href);

  /**
   * Gets the href to check.
   *
   * @return string
   */
  public function originalHref();

  /**
   * The corrected href.
   *
   * @return string
   */
  public function correctedHref();

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
   * The httpd code.
   *
   * @return int
   */
  public function getHttpCode();

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

  /**
   * Check if the href redirects to a taxonomy term.
   *
   * @return bool
   */
  public function redirectsToTerm();

  /**
   * Updates link if possible.
   * @return bool
   */
  public function update();

  /**
   * Strip link.
   *
   * @param bool $keep_link_text
   * @return bool
   */
  public function strip($keep_link_text = TRUE);

  /**
   * Replace link.
   *
   * @return bool
   */
  public function replace();

  /**
   * @return \DOMElement
   */
  public function element();

  /**
   * @return Field
   */
  public function getField();

  /**
   * @return Config
   */
  public function getConfig();
}
