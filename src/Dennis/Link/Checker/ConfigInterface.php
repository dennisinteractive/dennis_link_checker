<?php
/**
 * @file ConfigInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface ConfigInterface
 * @package Dennis\Link\Checker
 */
interface ConfigInterface {

  /**
   * The host domain of the site.
   *
   * @return
   */
  public function getSiteHost();

  /**
   * The host domain of the site.
   *
   * @param string
   * @return ConfigInterface
   */
  public function setSiteHost($str);

  /**
   * The maximum number of redirects to follow.
   *
   * @param $int
   * @return ConfigInterface
   */
  public function setMaxRedirects($int);

  /**
   * The maximum number of redirects to follow.
   *
   * @return int
   */
  public function getMaxRedirects();

  /**
   * Whether to check only local links
   *
   * @param $bool
   * @return ConfigInterface
   */
  public function setInternalOnly($bool);

  /**
   * Whether to check only local links
   *
   * @return boolean
   */
  public function internalOnly();

  /**
   * How the link should be changed for local links, if at all.
   *
   * @return integer
   *  A constant as defined in LinkLocalisation.
   */
  public function getLocalisation();

  /**
   * How the link should be changed for local links, if at all.
   *
   * @param integer
   *  A constant as defined in LinkLocalisation.
   * @return ConfigInterface
   */
  public function setLocalisation($int);

}
