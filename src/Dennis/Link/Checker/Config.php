<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Class Config
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Config implements ConfigInterface {

  protected $host;

  protected $maxRedirects;

  protected $localisation = LinkLocalisation::ORIGINAL;

  protected $internalOnly = TRUE;

  protected $logger;

  protected $removeTermLinks = TRUE;

  protected $removeFrontLinks = TRUE;

  protected $nids = FALSE;

  protected $fieldNames = [];

  /**
   * @inheritDoc
   */
  public function getSiteHost() {
    return $this->host;
  }

  /**
   * @inheritDoc
   */
  public function setSiteHost($str) {
    $this->host = $str;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setMaxRedirects($int) {
    $this->maxRedirects = (int) $int;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getMaxRedirects() {
    return $this->maxRedirects;
  }


  /**
   * @inheritDoc
   */
  public function setInternalOnly($bool) {
    $this->internalOnly = (bool) $bool;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function internalOnly() {
    return $this->internalOnly;
  }

  /**
   * @inheritDoc
   */
  public function getLocalisation() {
    return $this->localisation;
  }

  /**
   * @inheritDoc
   */
  public function setLocalisation($int) {
    $this->localisation = (int) $int;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getLogger() {
    return $this->logger;
  }

  /**
   * @inheritDoc
   */
  public function setRemoveTermLinks($remove) {
    $this->removeTermLinks = $remove;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function removeTermLinks() {
    return $this->removeTermLinks;
  }

  /**
   * @inheritDoc
   */
  public function setRemoveFrontLinks($remove) {
    $this->removeFrontLinks = $remove;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function removeFrontLinks() {
    return $this->removeFrontLinks;
  }

  /**
   * @inheritDoc
   */
  public function setNodeList(array $nids) {
    $this->nids = $nids;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getNodeList() {
    return $this->nids;
  }

  /**
   * @inheritDoc
   */
  public function setFieldNames(array $field_names) {
    $this->fieldNames = $field_names;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getFieldNames() {
    return $this->fieldNames;
  }

}
