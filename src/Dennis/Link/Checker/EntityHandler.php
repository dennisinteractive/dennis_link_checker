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

  protected $host;

  /**
   * @inheritDoc
   */
  public function setSiteHost($host) {
    $this->host = $host;
  }

  /**
   * @inheritDoc
   */
  public function getSiteHost() {
    return $this->host;
  }

  /**
   * @inheritDoc
   */
  public function findLinks($entity_type, $entity_id, $internal = TRUE) {

    $field_name = 'field_data_body';
    $value_field = 'body_value';

    $query = db_select($field_name, 't');
    $query->addField('t', $value_field);
    $query->condition('entity_id', $entity_id);
    $query->condition('entity_type', $entity_type);

    $result = $query->execute()->fetchObject();
    $text = $result->{$value_field};

    $site_host = $this->getSiteHost();
    $found = [];
    $dom = filter_dom_load($text);

    // Internal links: the number of manually-entered links in text fields to pages on the same site.
    // Note, some teams use absolute hrefs, some relative and some use node ids (eg: <a href="node/1404137">).
    $links = $dom->getElementsByTagName('a');
    foreach ($links as $link) {
      $href = $link->getAttribute('href');
      if ($internal) {
        if ($parsed = parse_url($href)) {
          if (empty($parsed['host']) || $parsed['host'] == $site_host) {
            $found[] = new Link($entity_type, $entity_id, $field_name, $href);
          }
        }
        else {
          $found[] = new Link($entity_type, $entity_id, $field_name, $href);
        }
      }
    }

    return $found;
  }

  /**
   * @inheritDoc
   */
  public function updateLink(LinkInterface $link) {

    $value_field = 'body_value'; //$todo not hard coded

    $query = db_select($link->entityField(), 't');
    $query->addField('t', $value_field);
    $query->condition('entity_id', $link->entityId());
    $query->condition('entity_type', $link->entityType());

    $result = $query->execute()->fetchObject();
    $text = $result->{$value_field};
    $text = str_replace($link->originalSrc(), $link->correctedSrc(), $text);

    db_update($link->entityField())
      ->fields(array(
        $value_field => $text
      ))
      ->condition('entity_id', $link->entityId())
      ->condition('entity_type', $link->entityType())
      ->execute();

    echo "  -- " . $link->correctedSrc() . "\n";
  }

}
