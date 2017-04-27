<?php
/**
 * @file
 * ConfigInterface
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

  /**
   * The logger to use.
   *
   * @param \Dennis\Link\Checker\LoggerInterface $logger
   * @return ConfigInterface
   */
  public function setLogger(LoggerInterface $logger);

  /**
   * The logger.
   *
   * @return \Dennis\Link\Checker\LoggerInterface $logger
   */
  public function getLogger();

  /**
   * Set if term links should be removed.
   *
   * @param $remove
   * @return mixed
   */
  public function setRemoveTermLinks($remove);

  /**
   * Check if term links should be removed.
   *
   * @return boolean
   */
  public function removeTermLinks();

  /**
   * Only Process the given nodes.
   *
   * @param array $nids
   * @return ConfigInterface
   */
  public function setNodeList(array $nids);

  /**
   * Return the list of nodes to limit to.
   *
   * @return array
   */
  public function getNodeList();
}
