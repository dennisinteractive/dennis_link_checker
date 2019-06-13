<?php
/**
 * @file
 * Analyzer
 */
namespace Dennis\Link\Checker;

/**
 * Class Analyzer
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
   * @var array Static cache of info from url calls.
   */
  protected $urlInfo = [];

  /**
   * The number of seconds to wait while trying to connect.
   */
  protected $connectionTimeout = 5;

  /**
   * The maximum number of seconds to allow cURL functions to execute.
   */
  protected $timeout = 10;

  /**
   * @var array An array of statistics which are used to analyse the previous
   * run.
   */
  protected $statistics = [];

  /**
   * @return \Dennis\Link\Checker\ConfigInterface
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * @param \Dennis\Link\Checker\ConfigInterface $config
   */
  public function setConfig($config) {
    $this->config = $config;
  }

  /**
   * @return number
   */
  public function getRedirectCount() {
    return $this->redirectCount;
  }

  /**
   * @param number $redirectCount
   */
  public function setRedirectCount($redirectCount) {
    $this->redirectCount = $redirectCount;
  }

  /**
   * @return int
   */
  public function getLinkTimeLimit() {
    return $this->linkTimeLimit;
  }

  /**
   * @param int $linkTimeLimit
   */
  public function setLinkTimeLimit($linkTimeLimit) {
    $this->linkTimeLimit = $linkTimeLimit;
  }

  /**
   * @return \Dennis\Link\Checker\Throttler
   */
  public function getCurlThrottler() {
    return $this->curlThrottler;
  }

  /**
   * @param \Dennis\Link\Checker\Throttler $curlThrottler
   */
  public function setCurlThrottler($curlThrottler) {
    $this->curlThrottler = $curlThrottler;
  }

  /**
   * @return \Dennis\Link\Checker\Database
   */
  public function getDatabase() {
    return $this->database;
  }

  /**
   * @param \Dennis\Link\Checker\Database $database
   */
  public function setDatabase($database) {
    $this->database = $database;
  }

  /**
   * @return array
   */
  public function getUrlInfo() {
    return $this->urlInfo;
  }

  /**
   * @param array $urlInfo
   */
  public function setUrlInfo($urlInfo) {
    $this->urlInfo = $urlInfo;
  }

  /**
   * @return mixed
   */
  public function getConnectionTimeout() {
    return $this->connectionTimeout;
  }

  /**
   * @param mixed $connectionTimeout
   */
  public function setConnectionTimeout($connectionTimeout) {
    $this->connectionTimeout = $connectionTimeout;
  }

  /**
   * @return mixed
   */
  public function getTimeout() {
    return $this->timeout;
  }

  /**
   * @param mixed $timeout
   */
  public function setTimeout($timeout) {
    $this->timeout = $timeout;
  }

  /**
   * @inheritDoc
   */
  public function getStatistics($array_key = NULL) {
    if (!empty($array_key)) {
      return !empty($this->statistics[$array_key]) ? $this->statistics[$array_key] : NULL;
    }

    return $this->statistics;
  }

  /**
   * @inheritDoc
   */
  public function setStatistics($statistics_or_array_key, $value = NULL) {
    if (is_string($statistics_or_array_key)) {
      $this->statistics[$statistics_or_array_key] = $value;
    }
    else {
      $this->statistics = $statistics_or_array_key;
    }
  }

  /**
   * @inheritDoc
   */
  public function updateStatistics($label, $value = NULL) {
    $statistics = $this->getStatistics();

    // If $value is NULL, we're recording an increment in a counter.
    if (is_null($value)) {
      $value = 1;
    }

    // If the statistic is new, create its array key.
    if (empty($statistics[$label])) {
      // If $value is a number, the statistic is going to be a counter;
      // otherwise, we assume it's going to be an array of strings - e.g. URLs.
      if (is_numeric($value)) {
        $statistics[$label] = 0;
      }
      else {
        $statistics[$label] = [];
      }
    }

    // Record the statistic.
    if (is_numeric($value)) {
      $statistics[$label] += $value;
    }
    else {
      $statistics[$label][] = $value;
    }

    // Update the statistics.
    $this->setStatistics($statistics);
  }

  /**
   * @inheritDoc
   */
  public function __construct(ConfigInterface $config, Throttler $curl_throttler, Database $database, array $statistics) {
    $this->config = $config;
    $this->curlThrottler = $curl_throttler;
    $this->database = $database;
    $this->setStatistics($statistics);
  }

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
   * Make sure we only process one link per configured number of seconds.
   */
  public function throttle() {
    $this->curlThrottler->throttle();
  }

  /**
   * @inheritDoc
   */
  public function link(LinkInterface $link) {
    $this->throttle();

    // Only redirect 301s so cannot use CURLOPT_FOLLOWLOCATION
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
   *
   * @return array
   * @throws ResourceFailException
   */
  public function getInfo($url) {
    $md5 = md5($url);
    if (isset($this->urlInfo[$md5])) {
      if (isset($this->urlInfo[$md5]['exception'])) {
        // Throw the exception again
        throw $this->urlInfo[$md5]['exception'];
      }
      return $this->urlInfo[$md5];
    }

    try {
      $this->urlInfo[$md5] = $this->doInfoRequest($url);
      return $this->urlInfo[$md5];
    } catch (ResourceFailException $e) {
      // Statically cache the exception happened.
      $this->urlInfo[$md5] = ['exception' => $e];
      // Re-throw the exception
      throw $e;
    }
  }

  /**
   * Performs a HEAD request.
   *
   * @param $url
   *
   * @return array
   * @throws ResourceFailException
   */
  protected function doInfoRequest($url) {
    // If url is protocol neutral, force it to use https.
    if (substr( $url, 0, 2 ) === "//") {
      $url = ltrim($url, '//');
      $url = 'https://' . $url;
    }
    // Only redirect 301s so cannot use CURLOPT_FOLLOWLOCATION
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'dennis_link_checker');
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

    // Don't check the certificate on the subject link; we're only checking for
    // broken links on an entertainment website, so we're not concerned about
    // certificates.

    // Also, note that if we try to validate the SSL cert when
    // running link checker through the admin UI on e.g. auth.evo.co.uk, and
    // cURL requests a page from www.evo.co.uk, in some circumstances the DNS
    // setup on Auth causes the request to loop back to the Auth server, which
    // presents the wrong SSL certificate - for auth.evo.co.uk, instead of
    // www.evo.co.uk - so the certificate check will fail as a result.

    // When this happens, the entire link check database is very quickly
    // polluted with failed link checks, error logs are filled with errors, and
    // it becomes very noisy in the Papertrail monitoring service, none of
    // which is ideal.

    // This is why we don't check the SSL certificates (-:
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

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
