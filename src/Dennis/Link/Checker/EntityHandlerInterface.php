<?php
/**
 * @file EntityHandlerInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface EntityHandlerInterface
 * @package Dennis\Link\Checker
 */
interface EntityHandlerInterface {

  /**
   * The entity id & type.
   *
   * @return array of LinkInterface
   */
  public function findLinks($entity_type, $entity_id, $internal = TRUE);

  /**
   * Gets the links from a string.
   *
   * @param $text
   * @return array of LinkInterface
   */
  public function getLinksFromText($text, $entity_type, $entity_id, $field_name, $site_host = NULL);

  /**
   * Change the link on the entity.
   *
   * @param LinkInterface $link
   * @param string $localisation
   *  How the link should be changed for local links, if at all
   * @return mixed
   */
  public function updateLink(LinkInterface $link, $localisation = NULL);

  /**
   * The host domain of the site.
   *
   * @param string $host
   * @return EntityHandlerInterface
   */
  public function setSiteHost($host);

  /**
   * Gets the host domain of the site.
   *
   * @return string
   */
  public function getSiteHost();

}
