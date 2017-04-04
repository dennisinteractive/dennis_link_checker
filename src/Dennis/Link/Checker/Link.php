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
  public function __construct($entity_type, $entity_id, $field, $src) {
    $this->setOriginalSrc($src);
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
  public function corrected() {
    if ($this->getNumberOfRedirects() > 0) {
      return TRUE;
    }

    return FALSE;
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
  public function correctedSrc() {
    return $this->data['found_url'];
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
