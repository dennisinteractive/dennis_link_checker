<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Interface LinkInterface.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface LinkInterface {

  /**
   * The number of redirects needed to get to the corrected url.
   *
   * @return int
   *   Returns the number of redirects.
   */
  public function getNumberOfRedirects();

  /**
   * Set the number of redirects needed to get to the corrected url.
   *
   * @param string $int
   *   The number of redirects to set.
   *
   * @return LinkInterface
   *   Returns the Link interface.
   */
  public function setNumberOfRedirects($int);

  /**
   * Whether the link was corrected.
   *
   * @return bool
   *   Returns TRUE if corrected.
   */
  public function corrected();

  /**
   * The href to check.
   *
   * @param string $href
   *   The original href to set.
   *
   * @return LinkInterface
   *   Returns the Link interface.
   */
  public function setOriginalHref($href);

  /**
   * Gets the href to check.
   *
   * @return string
   *   Returns the original href.
   */
  public function originalHref();

  /**
   * The corrected href.
   *
   * @return string
   *   Returns the corrected href.
   */
  public function correctedHref();

  /**
   * The url for this link that was found by the corrector.
   *
   * @param string $url
   *   Set found url.
   *
   * @return LinkInterface
   *   Returns the Link interface.
   */
  public function setFoundUrl($url);

  /**
   * Get the found URL.
   */
  public function getFoundUrl();

  /**
   * The httpd code.
   *
   * @param string $code
   *   Set the http code.
   *
   * @return LinkInterface
   *   Returns the Link interface.
   */
  public function setHttpCode($code);

  /**
   * The httpd code.
   *
   * @return int
   *   Returns the http coode.
   */
  public function getHttpCode();

  /**
   * The checker error.
   *
   * @param string $code
   *   The error code to set.
   * @param string $msg
   *   The error message to set.
   *
   * @return LinkInterface
   *   Returns the Link interface.
   */
  public function setError($code, $msg);

  /**
   * Get errors.
   *
   * @return array
   *   Returns an array of errors.
   */
  public function getError();

  /**
   * Check if the href redirects to a taxonomy term.
   *
   * @return bool
   *   Returns TRUE id it redirects to a term.
   */
  public function redirectsToTerm();

  /**
   * Check if the href redirects to a taxonomy term.
   *
   * @return bool
   *   Returns TRUE if redirects to front.
   */
  public function redirectsToFront();

  /**
   * Strip link.
   *
   * @param bool $keep_link_text
   *   Keep link text.
   *
   * @return bool
   *   Returns TRUE.
   */
  public function strip($keep_link_text = TRUE);

  /**
   * Replace link.
   *
   * @return bool
   *   Returns TRUE is element attribute is set, else FALSE.
   */
  public function replace();

  /**
   * Removes mce_href.
   */
  public function removeMceHref();

  /**
   * Link interface element.
   *
   * @return \DOMElement
   *   Returns a dom element.
   */
  public function element();

  /**
   * Get config.
   *
   * @return Config
   *   Returns the config.
   */
  public function getConfig();

}
