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
   * EntityHandlerInterface constructor.
   * @param ConfigInterface $config
   */
  public function __construct(ConfigInterface $config);

  /**
   * The entity id & type.
   *
   * @return array of LinkInterface
   */
  public function findLinks($entity_type, $entity_id);

  /**
   * Gets the links from a string.
   *
   * @param $text
   * @return array of LinkInterface
   */
  public function getLinksFromText($text, $entity_type, $entity_id, $field_name);

  /**
   * Change the link on the entity.
   *
   * @param LinkInterface $link
   * @return mixed
   */
  public function updateLink(LinkInterface $link);

  /**
   * Gets the host domain of the site.
   *
   * @return string
   */
  public function getSiteHost();

}
