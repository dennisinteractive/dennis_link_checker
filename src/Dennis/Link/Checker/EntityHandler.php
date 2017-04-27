<?php
/**
 * @file
 * EntityHandler
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
  public function setConfig(ConfigInterface $config) {
    $this->config = $config;

    return $this;
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

    // Before doing the replacement, check if the link originally pointed to a node, and
    // now points to a term, and if so then remove the link altogether. See case 27710.
    if ($this->config->removeTermLinks() && $link->redirectsToTerm()) {
      // Strip link and keep the text part
      $updated_text = $this->stripLink($link, $text);
      // If the updated text is different to the original, then the link was removed.
      if (strcmp($updated_text, $text) != 0) {
        $this->config->getLogger()->warning('LINK REMOVED : '
          . $link->entityType() . '/' . $link->entityId()
          . ' : ' . $link->originalHref() . " => $correction");
      }
      else {
        // Something went wrong, we could do more logging here
        // or just skip.
        return FALSE;
      }
    }
    else {
      //$updated_text = str_replace($link->originalHref(), $correction, $text);
      $updated_text = $this->replaceLink($text, $link->originalHref(), $correction);

      if (strcmp($updated_text, $text) != 0) {
        $this->config->getLogger()->info('Link corrected : '
          . $link->entityType() . '/' . $link->entityId()
          . ' : ' . $link->originalHref() . " => $correction");
      }
      else {
        $this->config->getLogger()->info('Link NOT corrected : '
          . $link->entityType() . '/' . $link->entityId()
          . ' : ' . $link->originalHref() . " => $correction");
        return FALSE;
      }
    }

    db_update($link->entityField())
      ->fields(array(
        $value_field => $updated_text
      ))
      ->condition('entity_id', $link->entityId())
      ->condition('entity_type', $link->entityType())
      ->execute();
  }

  /**
   * Helper function to remove a link from text and return the text.
   *
   * Default behavior is to keep the link text.
   *
   * @param $link
   * @param $text
   * @param $keep_link_text
   * @return mixed
   */
  public function stripLink($link, $text, $keep_link_text = TRUE) {
    // Approach is to find the href, then get the first instance of '<a' before
    // and the first instance of '/a>' after, and remove/replace the in-between.
    $href = $link->originalHref();
    // Update: If the href is mistakenly on any other tag, eg. <p> (actual case)
    // Then we should skip and report.
    $offset = 0;
    // It's possible that the link could appear multiple times in the text
    while ($pos = strpos($text, $href, $offset)) {
      // Strip the link only if we can find the full opening and closing tag
      // Find start of opening tag
      $start = strrpos($text, '<', ($pos - strlen($text)));
      if ($start !== FALSE) {
        // Make sure the next character after '<' is an 'a'
        if ($text[$start + 1] == 'a') {

          // Find end of opening tag
          $end = strpos($text, '>', $pos);
          if (!empty($end) && ($end > $start)) {
            // Find closing tag position
            $closing = strpos($text, '</a>', $pos);
            if (!empty($closing) && ($closing > $end)) {
              $snippit = substr($text, $start, ($closing - $start) + 4);
              $replace = '';
              if ($keep_link_text) {
                $replace = substr($text, $end + 1, ($closing - $end - 1));
              }
              $text = str_replace($snippit, $replace, $text);
            }
          }
        }
        else {
          $this->config->getLogger()->notice('BAD TAG : '
            . $link->entityType() . '/' . $link->entityId()
            . ' : ' . $link->originalHref() . " => " . $text[$start + 1]);
        }
      }

      $offset = $pos + 1;
    }

    return $text;
  }

  /**
   * Helper function to replace an exact href with another.
   *
   * @param $text
   * @param $find
   * @param $replace
   * @return string
   */
  public function replaceLink($text, $find, $replace) {
    $dom = filter_dom_load($text);

    $links = $dom->getElementsByTagName('a');
    foreach ($links as $link) {
      $href = $link->getAttribute('href');
      if (strcmp($find, $href) === 0) {
        $link->setAttribute('href', $replace);
      }
    }

    return $dom->saveHTML();
  }
}
