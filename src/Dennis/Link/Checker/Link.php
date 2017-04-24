<?php
/**
 * @file
 * Link
 */
namespace Dennis\Link\Checker;

/**
 * Class Link
 * @package Dennis\Link\Checker
 */
class Link implements LinkInterface {

  protected $config;

  protected $data = [];

  protected $tooManyRedirects = FALSE;

  /**
   * @inheritDoc
   */
  public function __construct(ConfigInterface $config, $entity_type, $entity_id, $field, $href) {
    $this->config = $config;
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

    if ($int > $this->config->getMaxRedirects()) {
      $this->setTooManyRedirects();
    }

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
  public function corrected() {
    if ($this->getHttpCode() != 200) {
      return FALSE;
    }

    if ($this->getNumberOfRedirects() > 0) {
      return TRUE;
    }

    // Check to see if the link as changed for another reason.
    if ($this->correctedHref() != $this->originalHref()) {
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
  public function correctedHref() {
    if (!empty($this->data['corrected_href'])) {
      return $this->data['corrected_href'];
    }

    // The found link will be an absolute one.
    // Default to the found url.
    $this->data['corrected_href'] = $this->getFoundUrl();

    // Keep links localised the way the editor saved them.
    if ($this->config->getLocalisation() == LinkLocalisation::ORIGINAL) {
      // Save it the same way it was originally if a local link.
      if ($parsed = parse_url($this->originalHref())) {
        if (empty($parsed['host'])) {
          if (!empty($parsed['path']) && $parsed['path'][0] == '/') {
            // Was originally a relative link.
            $this->data['corrected_href'] = $this->relativePath($parsed);
          }
        }
      }
      else {
        $this->data['corrected_href'] = $this->getFoundUrl();
      }
    }

    // Make all local links absolute.
    elseif ($this->config->getLocalisation() == LinkLocalisation::ABSOLUTE) {
      // Seo require local links to be absolute so if we get scrapped,
      // they will link back to us.
      $this->data['corrected_href'] = $this->getFoundUrl();
    }

    // Make all local links relative.
    elseif ($this->config->getLocalisation() == LinkLocalisation::RELATIVE
      && !empty($this->config->getSiteHost())) {
      // Check for a local link.
      if ($parsed = parse_url($this->getFoundUrl())) {
        if (!empty($parsed['host']) && $this->config->getSiteHost() == $parsed['host']) {
          // Make it relative.
          $this->data['corrected_href'] = $this->relativePath($parsed);;
        }
      }
    }

    // Make all local links protocol relative.
    elseif ($this->config->getLocalisation() == LinkLocalisation::PROTOCOL_RELATIVE
      && !empty($this->config->getSiteHost())) {
      // Check for a local link.
      if ($parsed = parse_url($this->getFoundUrl())) {
        if (!empty($parsed['host']) && $this->config->getSiteHost() == $parsed['host']) {
          // Make it relative.
          $this->data['corrected_href'] = '/' . $this->relativePath($parsed);
        }
      }
    }

    return $this->data['corrected_href'];
  }

  /**
   * Build the relative path from the output of parse_url().
   *
   * @param array $parsed
   * @return string
   */
  public function relativePath($parsed) {
    $path = isset($parsed['path']) ? $parsed['path'] : '';
    $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
    $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
    return "$path$query$fragment";
  }

  /**
   * @inheritDoc
   */
  public function setFoundUrl($url) {
    $this->data['found_url'] = $url;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getFoundUrl() {
    return isset($this->data['found_url']) ? $this->data['found_url'] : '';
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
  public function getHttpCode() {
    return isset($this->data['http_code']) ? $this->data['http_code'] : '';
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

  /**
   * Check of the href redirects to a taxonomy term.
   *
   * @return bool
   */
  public function redirectsToTerm() {
    if ($this->data['redirects_to_term']) {
      return $this->data['redirects_to_term'];
    }

    $this->data['redirects_to_term'] = FALSE;
    if ($this->correctedHref() != $this->originalHref()) {
      // This is definitely a redirected href
      // So the original href will either have a redirect record, or an alias
      $parsed = parse_url($this->originalHref());
      $original_path = ltrim($this->relativePath($parsed), '/');
      $internal_path = $this->getInternalPath($original_path);

      // If we found a internal path, try and get the entity type
      if (!empty($internal_path)) {
        if ($this->typeFromPath($internal_path) == 'node') {

          // Check the corrected path entity type
          $parsed = parse_url($this->correctedHref());
          $corrected_path = ltrim($this->relativePath($parsed), '/');

          // Corrected path should be a current alias
          $internal_path = $this->getInternalPath($corrected_path);
          if (!empty($internal_path)) {
            if ($this->typeFromPath($internal_path) == 'taxonomy_term') {
              $this->data['redirects_to_term'] = TRUE;
            }
          }
        }
      }
    }

    return $this->data['redirects_to_term'];
  }

  /**
   * Helper function to try find a record for a given path.
   *
   * @param $path
   * @return bool|mixed
   */
  private function getInternalPath($path) {
    $internal_path = FALSE;
    // Check for redirect
    $redirect = redirect_load_by_source($path);
    if (!empty($redirect)) {
      $internal_path = $redirect->redirect;
    }

    // Check for alias
    if (empty($internal_path)) {
      $internal_path = drupal_get_normal_path($path);
    }

    return $internal_path;
  }


  private function typeFromPath($path) {

    $entity_type = FALSE;

    // Strip off leading /
    $path = ltrim($path, '/');
    // Grab the first element of path to determine entity type (no need to load the whole entity)
    $parts = explode('/', $path);
    if (!empty($parts)) {
      $entity_type = reset($parts);
      // Special handling for taxonomy terms
      if ($entity_type == 'taxonomy') {
        $bundle = isset($parts[1]) ? $parts[1] : FALSE;
        if ($bundle == 'term') {
          $entity_type = $entity_type . '_' . $bundle;
        }
      }
    }

    return $entity_type;
  }
}
