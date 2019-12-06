<?php

namespace Drupal\dennis_link_checker\Dennis\Asset\Checker;

use Drupal\dennis_link_checker\Dennis\Link\Checker\Link;
use Drupal\dennis_link_checker\Dennis\Link\Checker\ConfigInterface;
use Drupal\dennis_link_checker\CheckerManagers;


/**
 * Class AssetField
 *
 * @package Dennis\Asset\Checker
 *
 * To avoid a lot of very similar code this extends link.
 */
class Asset extends Link {

  protected $data = [];

  /**
   * Asset constructor.
   *
   * @param CheckerManagers $checkerManagers
   * @param ConfigInterface $config
   * @param $href
   * @param \DOMElement $element
   */
  public function __construct(
    CheckerManagers $checkerManagers,
    ConfigInterface $config,
    $href,
    \DOMElement $element) {

    parent::__construct(
      $checkerManagers,
      $config,
      $href,
      $element
    );

    $this->setOriginalSrc($href);
    $this->config = $config;
    $this->data['element'] = $element;
  }

  /**
   * @inheritDoc
   */
  public function setOriginalSrc($src) {
    $this->data['original_src'] = $src;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function originalSrc() {
    return $this->data['original_src'];
  }

  /**
   * @inheritDoc
   */
  public function remove() {
    $this->element()->parentNode->removeChild($this->element());
    return TRUE;
  }

  /**
   * Custom function which just compares the original source with the URL thats been found.
   */
  public function corrected() {
    return $this->originalSrc() != $this->getFoundUrl() ? TRUE : FALSE;
  }

  /**
   * Replace src of an asset if necessary.
   */
  public function replace() {
    if ($this->getFoundUrl() != $this->originalSrc()) {
      $this->element()->setAttribute('src', $this->getFoundUrl());
      return TRUE;
    }
    return FALSE;
  }
}
