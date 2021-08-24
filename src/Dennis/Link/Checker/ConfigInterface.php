<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Interface ConfigInterface.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface ConfigInterface {

  /**
   * The host domain of the site.
   */
  public function getSiteHost();

  /**
   * The host domain of the site.
   *
   * @param string $str
   *   Site host.
   */
  public function setSiteHost($str);

  /**
   * The maximum number of redirects to follow.
   *
   * @param int $int
   *   Number of redirects.
   */
  public function setMaxRedirects($int);

  /**
   * The maximum number of redirects to follow.
   */
  public function getMaxRedirects();

  /**
   * Whether to check only local links.
   *
   * @param bool $bool
   *   Set internaal only.
   */
  public function setInternalOnly($bool);

  /**
   * Whether to check only local links.
   */
  public function internalOnly();

  /**
   * How the link should be changed for local links, if at all.
   *
   * @return int
   *   A constant as defined in LinkLocalisation.
   */
  public function getLocalisation();

  /**
   * How the link should be changed for local links, if at all.
   *
   * @param int $int
   *   A constant as defined in LinkLocalisation.
   */
  public function setLocalisation($int);

  /**
   * The logger to use.
   *
   * @param \Drupal\dennis_link_checker\Dennis\Link\Checker\LoggerInterface $logger
   *   Logger interface.
   */
  public function setLogger(LoggerInterface $logger);

  /**
   * The logger.
   *
   * @return \Drupal\dennis_link_checker\Dennis\Link\Checker\LoggerInterface
   *   Returns the logger interface.
   */
  public function getLogger();

  /**
   * Set if term links should be removed.
   *
   * @param bool $remove
   *   Set to TRUE too remove links.
   */
  public function setRemoveTermLinks($remove);

  /**
   * Check if term links should be removed.
   */
  public function removeTermLinks();

  /**
   * Only Process the given nodes.
   *
   * @param array $nids
   *   Array of nids.
   */
  public function setNodeList(array $nids);

  /**
   * Return the list of nodes to limit to.
   */
  public function getNodeList();

  /**
   * The names of fields to check for links in.
   *
   * @param array $field_names
   *   Array of field names.
   */
  public function setFieldNames(array $field_names);

  /**
   * The names of fields to check for links in.
   */
  public function getFieldNames();

  /**
   * Check if front links should be removed.
   */
  public function removeFrontLinks();

}
