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
   * The number of redirects needed to get to the corrected url.
   *
   * @return integer
   */
  public function getNumberOfRedirects();

  /**
   * Set the number of redirects needed to get to the corrected url.
   *
   * @param $redirect_count
   *
   * @return LinkInterface
   */
  public function setNumberOfRedirects($redirect_count);

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
   * @return Config
   */
  public function getConfig();
}
