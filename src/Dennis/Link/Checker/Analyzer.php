<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

/**
 * Class Analyzer.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Analyzer implements AnalyzerInterface {

  /**
   * Config interface.
   *
   * @var ConfigInterface
   */
  protected $config;

  /**
   * Number of redirects in current chain.
   *
   * @var int
   */
  protected $redirectCount;

  /**
   * Maximum number of seconds to spend resolving links.
   *
   * @var int
   */
  protected $linkTimeLimit = 480;

  /**
   * Throttler.
   *
   * @var Throttler
   */
  protected $curlThrottler;

  /**
   * Database.
   *
   * @var Database
   */
  protected $database;

  /**
   * Static cache of info from url calls.
   *
   * @var array
   */
  protected $urlInfo = [];

  /**
   * The number of seconds to wait while trying to connect.
   *
   * @var int
   */
  protected $connectionTimeout = 5;

  /**
   * The maximum number of seconds to allow cURL functions to execute.
   *
   * @var int
   */
  protected $timeout = 10;

  /**
   * {@inheritDoc}
   */
  public function __construct(ConfigInterface $config, Throttler $curl_throttler, Database $database) {
    $this->config = $config;
    $this->curlThrottler = $curl_throttler;
    $this->database = $database;
  }

  /**
   * {@inheritDoc}
   */
  public function getSiteHost() {
    return $this->config->getSiteHost();
  }

  /**
   * {@inheritDoc}
   */
  public function multipleLinks(array $links) {
    $current_timeout = $this->linkTimeLimit + time();
    foreach ($links as $link) {
      if (time() >= $current_timeout) {
        throw new TimeoutException(sprintf('Could not process %s links within %s seconds',
          count($links),
          $this->linkTimeLimit));
      }
      $this->link($link);

      // Keep the DB connection alive whilst we are processing external links.
      $this->database->keepConnectionAlive();
    }

    return $links;
  }

  /**
   * Make sure we only process one link per configured number of seconds.
   */
  public function throttle() {
    $this->curlThrottler->throttle();
  }

  /**
   * {@inheritDoc}
   */
  public function link(LinkInterface $link) {
    $this->throttle();

    // Only redirect 301's so cannot use CURLOPT_FOLLOWLOCATION.
    $this->redirectCount = 0;

    $src = trim($link->originalHref());
    if (!$host = parse_url($src, PHP_URL_HOST)) {
      $host = $this->getSiteHost();
      $url = $host . '/' . ltrim($src, '/');
    }
    else {
      $url = $src;
    }
    // If url is protocol neutral, force it to use http.
    if (substr($url, 0, 2) === "//") {
      $url = ltrim($url, '//');
      $url = 'http://' . $url;
    }
    try {
      $info = $this->followRedirects($url);
      $link->setFoundUrl($info['url'])
        ->setHttpCode($info['http_code'])
        ->setNumberOfRedirects($this->redirectCount);
    }
    catch (ResourceFailException $e) {
      $link->setNumberOfRedirects($this->redirectCount)
        ->setError($e->getMessage(), $e->getCode());

      // If the request timed out,
      // throw a RequestTimeoutException so the processor can give up for this process.
      if ($e->getCode() == CURLE_OPERATION_TIMEDOUT || $e->getCode() == CURLOPT_TIMEOUT) {
        throw new RequestTimeoutException($e->getMessage(), $e->getCode());
      }
    }

    return $link;
  }

  /**
   * Recursively follow 301 redirects only.
   *
   * @param string $url
   *   The url to follow.
   *
   * @return array
   *   The curl_getinfo() array.
   */
  protected function followRedirects($url) {
    $info = $this->getInfo($url);

    if (!empty($info['redirect_url'])) {
      if ($info['http_code'] == 301) {
        // Throw exception if we have reached our redirect limit.
        if ($this->redirectCount > $this->config->getMaxRedirects()) {
          throw new ResourceFailException(sprintf('Maximum of %s redirects reached.', $this->config->getMaxRedirects()));
        }
        // Do the redirect.
        $this->redirectCount++;
        return $this->followRedirects($info['redirect_url']);
      }
    }

    return $info;
  }

  /**
   * Makes an http call and returns info about what it found.
   *
   * @param string $url
   *   The url to get info from.
   *
   * @return array
   *   Returns an array of url info.
   *
   * @throws ResourceFailException
   */
  public function getInfo($url) {
    $md5 = md5($url);
    if (isset($this->urlInfo[$md5])) {
      if (isset($this->urlInfo[$md5]['exception'])) {
        // Throw the exception again.
        throw $this->urlInfo[$md5]['exception'];
      }
      return $this->urlInfo[$md5];
    }

    try {
      $this->urlInfo[$md5] = $this->doInfoRequest($url);
      return $this->urlInfo[$md5];
    }
    catch (ResourceFailException $e) {
      // Statically cache the exception happened.
      $this->urlInfo[$md5] = ['exception' => $e];
      // Re-throw the exception.
      throw $e;
    }
  }

  /**
   * Performs a HEAD request.
   *
   * @param string $url
   *   The url to curl.
   *
   * @return array
   *   Returns array of curl info.
   *
   * @throws ResourceFailException
   */
  protected function doInfoRequest($url) {
    // Only redirect 301's so cannot use CURLOPT_FOLLOWLOCATION.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'dennis_link_checker');
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

    if (curl_exec($ch)) {
      $info = curl_getinfo($ch);
      curl_close($ch);
      return $info;
    }
    else {
      $errno = curl_errno($ch);
      $error = curl_error($ch);
      curl_close($ch);
      throw new ResourceFailException($error, $errno);
    }
  }

}
