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
  public function corrected() {
    // @todo
  }

  /**
   * @inheritDoc
   */
  public function setOriginalSrc($src) {
    $this->data['original_src'] = trim($src);

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
  public function setCorrectedSrc($src) {
    $this->data['corrected_src'] = $src;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function correctedSrc() {
    return $this->data['corrected_src'];
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
  public function setHttpCode($code) {
    $this->data['http_code'] = $code;

    return $this;
  }


}
