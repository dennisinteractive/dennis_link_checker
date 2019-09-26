<?php

/**
 * @file
 * Asset
 */
namespace Dennis\Asset\Checker;
use Dennis\Link\Checker\Link;
use Dennis\Link\Checker\ConfigInterface;


/**
 * Class AssetField
 * @package Dennis\Asset\Checker
 *
 * To avoid a lot of very similar code this extends link.
 */
class Asset extends Link {
  protected $data = [];

  /**
   * @inheritDoc
   */
  public function __construct(ConfigInterface $config, $src, \DOMElement $element) {
    $this->setOriginalSrc($src);
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

  /*
   * Custom function which just compares the original source with the URL thats been found.
   */
  public function corrected() {
    if($this->originalSrc() != $this->getFoundUrl()) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /*
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