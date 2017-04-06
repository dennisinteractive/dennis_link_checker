<?php
/**
 * @file Analyzer
 */
namespace Dennis\Link\Checker;

/**
 * Class Corrector
 * @package Dennis\Link\Checker
 */
class Analyzer implements AnalyzerInterface {

  protected $host;

  protected $config;

  /**
   * @inheritDoc
   */
  public function __construct(ConfigInterface $config) {
    $this->config = $config;
  }

  /**
   * The number of seconds to wait while trying to connect.
   */
  protected $connectionTimeout = 5;

  /**
   * The maximum number of seconds to allow cURL functions to execute.
   */
  protected $timeout = 10;

  /**
   * @inheritDoc
   */
  public function getSiteHost() {
    return $this->config->getSiteHost();
  }

  /**
   * @inheritDoc
   */
  public function link(LinkInterface $link) {

    $src = trim($link->originalHref());
    if (!$host = parse_url($src, PHP_URL_HOST)) {
      $host = $this->getSiteHost();
      $url = $host . '/' . ltrim($src, '/');
    }
    else {
      $url = $src;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'dennis_link_checker');
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);

    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);


    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

    if ($headers = curl_exec($ch)) {
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
