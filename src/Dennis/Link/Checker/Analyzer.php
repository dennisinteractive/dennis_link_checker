<?php
/**
 * @file
 * Analyzer
 */
namespace Dennis\Link\Checker;

/**
 * Class Corrector
 * @package Dennis\Link\Checker
 */
class Analyzer implements AnalyzerInterface {
  /**
   * @var ConfigInterface
   */
  protected $config;

  /**
   * @var number of redirects in current chain.
   */
  protected $redirectCount;

  /**
   * @var int maximum number of seconds to spend resolving links.
   */
  protected $linkTimeLimit = 480;

  /**
   * @var Throttler
   */
  protected $curlThrottler;

  /**
   * @var Database
   */
  protected $database;

  /**
   * @inheritDoc
   */
  public function __construct(ConfigInterface $config, Throttler $curl_throttler, Database $database) {
    $this->config = $config;
    $this->curlThrottler = $curl_throttler;
    $this->database = $database;
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
  public function multipleLinks($links) {
    $timeout = $this->linkTimeLimit + time();

    foreach ($links as $link) {
      if (time() >= $timeout) {
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
   * @inheritDoc
   */
  public function link(LinkInterface $link) {
    // Make sure we only process one link per configured number of seconds.
    $this->curlThrottler->throttle();

    // Only redirect 301's so cannot use CURLOPT_FOLLOWLOCATION
    $this->redirectCount = 0;

    $src = trim($link->originalHref());
    if (!$host = parse_url($src, PHP_URL_HOST)) {
      $host = $this->getSiteHost();
      $url = $host . '/' . ltrim($src, '/');
    }
    else {
      $url = $src;
    }

    try {
      $info = $this->followRedirects($url);
      $link->setFoundUrl($info['url'])
        ->setHttpCode($info['http_code'])
        ->setNumberOfRedirects($this->redirectCount);

    } catch (ResourceFailException $e) {
      $link->setNumberOfRedirects($this->redirectCount)
        ->setError($e->getMessage(), $e->getCode());
    }

    return $link;
  }

  /**
   * Recursively follow 301 redirects only.
   *
   * @param $url
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
        // Do the redirect
        $this->redirectCount++;
        return $this->followRedirects($info['redirect_url']);
      }
    }

    return $info;
  }

  /**
   * Makes an http call and returns info about what it found.
   *
   * @param $url
   * @return array
   */
  protected function getInfo($url) {
    // Only redirect 301's so cannot use CURLOPT_FOLLOWLOCATION
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
      curl_close($ch);
      throw new ResourceFailException(curl_error($ch), curl_errno($ch));
    }

  }

}
