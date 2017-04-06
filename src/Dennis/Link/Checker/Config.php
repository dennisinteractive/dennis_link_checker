<?php
/**
 * @file Config
 */
namespace Dennis\Link\Checker;

/**
 * Class Config
 * @package Dennis\Link\Checker
 */
class Config implements ConfigInterface {

  protected $host;

  protected $maxRedirects;

  protected $localisation;

  protected $internalOnly = TRUE;

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

}
