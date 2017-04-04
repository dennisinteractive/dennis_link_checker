<?php
/**
 * @file Item
 */
namespace Dennis\Link\Checker;

/**
 * Class Corrector
 * @package Dennis\Link\Checker
 */
class Corrector implements CorrectorInterface {

  protected $host;

  /**
   * The number of seconds to wait while trying to connect.
   */
  protected $connectionTimeout = 10;

  /**
   * The maximum number of seconds to allow cURL functions to execute.
   */
  protected $timeout = 30;

  /**
   * @inheritDoc
   */
  public function setSiteHost($host) {
    $this->host = $host;
  }

  /**
   * @inheritDoc
   */
  public function getSiteHost() {
    return $this->host;
  }

  /**
   * @inheritDoc
   */
  public function link(LinkInterface $link) {

    $src = trim($link->originalSrc());
    // Oddness fix.
    $src = str_replace(array('%20http:', '%22http:'), 'http:', $src);
    if (!$host = parse_url($src, PHP_URL_HOST)) {
      $host = $this->getSiteHost();
      $url = $host . '/' . ltrim($src, '/');
    }
    else {
      $url = $src;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

    if (curl_exec($ch)) {
      $info = curl_getinfo($ch);
      $link->setFoundUrl($info['url'])
        ->setHttpCode($info['http_code'])
        ->setNumberOfRedirects($info['redirect_count']);

    }
    else {
      if (curl_errno($ch) == CURLE_TOO_MANY_REDIRECTS) {
        // Curl error: Maximum (10) redirects followed - number: 47
        $link->setTooManyRedirects();
      }
      $link->setError(curl_error($ch), curl_errno($ch));
    }
    curl_close($ch);

    return $link;
  }

  /**
   * @inheritDoc
   */
  public function multipleLinks($links) {
    foreach ($links as &$link) {
      $link = $this->link($link);
    }

    return $links;
  }

}
