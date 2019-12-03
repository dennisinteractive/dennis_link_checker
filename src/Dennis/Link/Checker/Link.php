<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Core\Database\Connection;

/**
 * Class Link
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Link implements LinkInterface {

  /**
   * @var array
   */
  protected $data = [];

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * @var ConfigInterface
   */
  protected $config;

  /**
   * @inheritDoc
   */
  public function __construct(Connection $connection, ConfigInterface $config, $href, \DOMElement $element) {
    $this->setOriginalHref($href);
    $this->connection = $connection;
    $this->config = $config;
    $this->data['element'] = $element;
  }

  /**
   * @inheritDoc
   */
  public function getConfig() {
    return $this->config;
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
  public function element() {
    return $this->data['element'];
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
    if ($this->getConfig()->getLocalisation() == LinkLocalisation::ORIGINAL) {
      // Save it the same way it was originally if a local link.
      if ($parsed = parse_url($this->originalHref())) {
        if (empty($parsed['host'])) {
          if (!empty($parsed['path']) && $parsed['path'][0] == '/') {
            // Was originally a relative link.
            $parsed = parse_url($this->getFoundUrl());
            $this->data['corrected_href'] = $this->relativePath($parsed);
          }
        }
      }
      else {
        $this->data['corrected_href'] = $this->getFoundUrl();
      }
    }

    // Make all local links absolute.
    elseif ($this->getConfig()->getLocalisation() == LinkLocalisation::ABSOLUTE) {
      // Seo require local links to be absolute so if we get scrapped,
      // they will link back to us.
      $this->data['corrected_href'] = $this->getFoundUrl();
    }

    // Make all local links relative.
    elseif ($this->getConfig()->getLocalisation() == LinkLocalisation::RELATIVE
      && !empty($this->getConfig()->getSiteHost())) {
      // Check for a local link.
      if ($parsed = parse_url($this->getFoundUrl())) {
        if (!empty($parsed['host']) && $this->getConfig()->getSiteHost() == $parsed['host']) {
          // Make it relative.
          $this->data['corrected_href'] = $this->relativePath($parsed);
        }
      }
    }

    // Make all local links protocol relative.
    elseif ($this->getConfig()->getLocalisation() == LinkLocalisation::PROTOCOL_RELATIVE
      && !empty($this->getConfig()->getSiteHost())) {
      // Check for a local link.
      if ($parsed = parse_url($this->getFoundUrl())) {
        if (!empty($parsed['host']) && $this->getConfig()->getSiteHost() == $parsed['host']) {
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
        } else {
          // Alias entity type can not be established.
          $this->getConfig()->getLogger()->warning('ENTITY TYPE COULD NOT BE DETERMINED: ' . $internal_path);
        }
      }
    }

    return $this->data['redirects_to_term'];
  }

  /**
   * Check of the href redirects to a <front>.
   *
   * @return bool
   */
  public function redirectsToFront() {
    $parsed_url = parse_url($this->originalHref());
    $baseurl = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/';

    if ($this->data['redirects_to_home']) {
      return $this->data['redirects_to_home'];
    }

    $this->data['redirects_to_home'] = FALSE;

    if ($this->correctedHref() != $this->originalHref()) {
      if (in_array($this->correctedHref(), ['/', $baseurl])) {
        $this->data['redirects_to_home'] = TRUE;
      }
    }
    return $this->data['redirects_to_home'];
  }

  /**
   * Helper function to try find a record for a given path.
   *
   * @param $path
   * @return bool
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getInternalPath($path) {
    $internal_path = FALSE;
    // Check for redirect
    $redirects = new Redirects($this->connection);
    $redirect = $redirects->redirectLoadBySource($path);
    if (!empty($redirect)) {
      $internal_path = $redirect->redirect;
    }

    // Check for alias
    if (empty($internal_path)) {
      $default_value = \Drupal::languageManager()->getDefaultLanguage()->getId();
      $internal_path = \Drupal::service('path.alias_manager')->getPathByAlias($path, $default_value);
    }

    return $internal_path;
  }


  /**
   * Helper function to try work out the entity type from the first component of a path.
   *
   * @param $path
   * @return bool|mixed|string
   */
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

  /**
   * Suggest a possible alternative to the given href.
   *
   * @param $href
   * @return bool|string
   */
  public function suggestLink($href) {
    $parsed = parse_url($href);
    $path = ltrim($this->relativePath($parsed), '/');
    // The href passed in is a 404, so we can't lookup the alias.
    // instead, we will look at the first component of the path that is a number,
    // and get the active alias for that node. Note that we assume node here.
    $parts = explode('/', $path);
    if (count($parts)) {
      foreach ($parts as $part) {
        if (is_numeric($part)) {
          return \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $part);
        }
      }
    }

    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function strip($keep_link_text = TRUE) {
    if ($keep_link_text) {
      if ($this->element()->hasChildNodes()) {
        foreach ($this->element()->childNodes as $childNode) {
          $newChild = clone $childNode;
          $this->element()->parentNode->insertBefore($newChild, $this->element());
        }
      }
    }
    $this->element()->parentNode->removeChild($this->element());
    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function replace() {
    if ($this->correctedHref() != $this->originalHref()) {
      $this->element()->setAttribute('href', $this->correctedHref());
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Remove attribute mce_href, added by tinymce.
   * @return bool
   */
  public function removeMceHref() {
    if (!empty($this->element()->getAttribute('mce_href'))) {
      $this->element()->removeAttribute('mce_href');
      return TRUE;
    }
    return FALSE;
  }
}
