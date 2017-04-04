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
  public function findLinks($entity_type, $entity_id) {

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
      if ($href = $link->attributes->getNamedItem("href")) {
        $host = parse_url($href->nodeValue, PHP_URL_HOST);
        if (empty($host) || $host == $site_host) {
          $found[] = new Link($entity_type, $entity_id, $field_name, $href->nodeValue);
        }
      }
    }

    return $found;
  }

}
