<?php
/**
 * @file Item
 */
namespace Dennis\Link\Checker;

/**
 * Class EntityHandler
 * @package Dennis\Link\Checker
 */
class EntityHandler implements EntityHandlerInterface {

  protected $config;

  /**
   * @inheritDoc
   */
  public function __construct(ConfigInterface $config) {
    $this->config = $config;
  }

  /**
   * @inheritDoc
   */
  public function getSiteHost() {
    return $this->config->getSiteHost();
  }

  /**
   * @inheritDoc
   */
  public function findLinks($entity_type, $entity_id) {

    $field_name = 'field_data_body';
    $value_field = 'body_value';

    $query = db_select($field_name, 't');
    $query->addField('t', $value_field);
    $query->condition('entity_id', $entity_id);
    $query->condition('entity_type', $entity_type);

    $result = $query->execute()->fetchObject();
    $text = $result->{$value_field};

    return $this->getLinksFromText($text, $entity_type, $entity_id, $field_name);
  }

  /**
   * @inheritDoc
   */
  public function getLinksFromText($text, $entity_type, $entity_id, $field_name) {
    $found = [];
    $dom = filter_dom_load($text);

    // Internal links: the number of manually-entered links in text fields to pages on the same site.
    // Note, some teams use absolute hrefs, some relative and some use node ids (eg: <a href="node/1404137">).
    $links = $dom->getElementsByTagName('a');
    foreach ($links as $link) {
      $href = $link->getAttribute('href');
      if ($this->config->internalOnly()) {
        // Only get local links.
        if ($parsed = parse_url($href)) {
          if (empty($parsed['host'])) {
            if (!empty($parsed['path']) && $parsed['path'][0] == '/') {
              // A valid local link.
              $found[] = new Link($this->config, $entity_type, $entity_id, $field_name, $href);
            }
          }
          elseif ($parsed['host'] == $this->getSiteHost()) {
            // A full url, but local
            $found[] = new Link($this->config, $entity_type, $entity_id, $field_name, $href);
          }
        }
      }
      else {
        // All links.
        $found[] = new Link($this->config, $entity_type, $entity_id, $field_name, $href);
      }
    }

    return $found;
  }

  /**
   * @inheritDoc
   */
  public function updateLink(LinkInterface $link) {

    $value_field = 'body_value'; //@todo not hard coded

    $query = db_select($link->entityField(), 't');
    $query->addField('t', $value_field);
    $query->condition('entity_id', $link->entityId());
    $query->condition('entity_type', $link->entityType());

    $result = $query->execute()->fetchObject();
    $text = $result->{$value_field};
    $correction = $link->correctedHref();
    $text = str_replace($link->originalHref(), $correction, $text);

    db_update($link->entityField())
      ->fields(array(
        $value_field => $text
      ))
      ->condition('entity_id', $link->entityId())
      ->condition('entity_type', $link->entityType())
      ->execute();

    echo "  -- $correction\n";
  }

}
