<?php
/**
 * @file Link
 */
namespace Dennis\Link\Checker;

/**
 * Class Link
 * @package Dennis\Link\Checker
 */
class Link implements LinkInterface {

  protected $data = [];

  protected $tooManyRedirects = FALSE;

  /**
   * @inheritDoc
   */
  public function __construct($entity_type, $entity_id, $field, $href) {
    $this->setOriginalHref($href);
    $this->data['entity_type'] = $entity_type;
    $this->data['entity_id'] = $entity_id;
    $this->data['field'] = $field;
  }

  /**
   * @inheritDoc
   */
  public function entityType() {
    return $this->data['entity_type'];
  }

  /**
   * @inheritDoc
   */
  public function entityId() {
    return $this->data['entity_id'];
  }

  /**
   * @inheritDoc
   */
  public function entityField() {
    return $this->data['field'];
  }

  /**
   * @inheritDoc
   */
  public function getNumberOfRedirects() {
    return $this->data['redirect_count'];
  }

  /**
   * @inheritDoc
   */
  public function setNumberOfRedirects($int) {
    $this->data['redirect_count'] = (int) $int;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setTooManyRedirects() {
    $this->tooManyRedirects = TRUE;
  }

  /**
   * @inheritDoc
   */
  public function hasTooManyRedirects() {
    return $this->tooManyRedirects;
  }


  /**
   * @inheritDoc
   */
  public function corrected($site_host = NULL, $localisation = NULL) {
    if ($this->getNumberOfRedirects() > 0) {
      return TRUE;
    }

    // Check to see if the link as changed for another reason.
    if ($this->correctedHref($site_host, $localisation) != $this->originalHref()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function setOriginalHref($href) {
    $this->data['original_href'] = $href;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function originalHref() {
    return $this->data['original_href'];
  }

  /**
   * @inheritDoc
   */
  public function correctedHref($site_host = NULL, $localisation = NULL) {
    // The found link will be an absolute one.

    // Keep links localised the way the editor saved them.
    if (!empty($site_host) && empty($localisation)) {
      // Save it the same way it was originally if a local link.
      if ($parsed = parse_url($this->originalHref())) {
        if (empty($parsed['host'])) {
          if (!empty($parsed['path']) && $parsed['path'][0] == '/') {
            // Was originally a relative link.
            $path = isset($parsed['path']) ? $parsed['path'] : '';
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
            return "$path$query$fragment";
          }
        }
      }

      return $this->getFoundUrl();
    }

    // Make all local links absolute.
    if ($localisation == 'absolute') {
      // Seo require local links to be absolute so if we get scrapped,
      // they will link back to us.
      return $this->getFoundUrl();
    }

    // Make all local links relative.
    if (!empty($site_host) && $localisation == 'relative') {
      // Check for a local link.
      if ($parsed = parse_url($this->data['found_url'])) {
        if (!empty($parsed['host']) && $site_host == $parsed['host']) {
          // Make it relative.
          $path = isset($parsed['path']) ? $parsed['path'] : '';
          $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
          $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
          return "$path$query$fragment";
        }
      }
    }

    return $this->getFoundUrl();
  }

  /**
   * @inheritDoc
   */
  public function setFoundUrl($url) {
    $this->data['found_url'] = $url;

    return $this;
  }

  public function getFoundUrl() {
    return $this->data['found_url'];
  }

  /**
   * @inheritDoc
   */
  public function setHttpCode($code) {
    $this->data['http_code'] = $code;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setError($code, $msg) {
    $this->data['error']['code'] = $code;
    $this->data['error']['msg'] = $msg;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getError() {
    if (isset($this->data['error'])) {
      return $this->data['error'];
    }

    return FALSE;
  }

}
