<?php

namespace Drupal\dennis_link_checker\Dennis\Asset\Checker;

use Drupal\dennis_link_checker\Dennis\Link\Checker\Analyzer;
use Drupal\dennis_link_checker\Dennis\Link\Checker\TimeoutException;
use Drupal\dennis_link_checker\Dennis\Link\Checker\ResourceFailException;
use Drupal\dennis_link_checker\Dennis\Link\Checker\RequestTimeoutException;

/**
 * Class AssetField.
 *
 * @package Dennis\Asset\Checker
 */
class AssetAnalyser extends Analyzer {

  /**
   * Checks an array of assets.
   *
   * @param array $assets
   *   An array of Asset assets.
   *
   * @return mixed
   *   Returns an array of assets.
   *
   * @throws \Drupal\dennis_link_checker\Dennis\Link\Checker\RequestTimeoutException
   * @throws \Drupal\dennis_link_checker\Dennis\Link\Checker\TimeoutException
   */
  public function multipleAssets(array $assets) {
    $timeout = $this->linkTimeLimit + time();

    foreach ($assets as $asset) {
      if (time() >= $timeout) {
        throw new TimeoutException(sprintf('Could not process %s links within %s seconds',
          count($asset),
          $this->linkTimeLimit));
      }
      $this->asset($asset);

      // Keep the DB connection alive whilst we are processing external links.
      $this->database->keepConnectionAlive();
    }
    return $assets;
  }

  /**
   * Borrowed heavily from Analyser link(). If asset is a redirect or not secure update url.
   *
   * @param Asset $asset
   *   The asset object.
   *
   * @return Asset
   *   The asset object.
   *
   * @throws \Drupal\dennis_link_checker\Dennis\Link\Checker\RequestTimeoutException
   */
  public function asset(Asset $asset) {
    $this->throttle();

    // Only redirect 301's so cannot use CURLOPT_FOLLOWLOCATION.
    $this->redirectCount = 0;

    $src = trim($asset->originalSrc());
    if (!$host = parse_url($src, PHP_URL_HOST)) {
      $host = $this->getSiteHost();
      $url = $host . '/' . ltrim($src, '/');
    }
    else {
      $url = $src;
    }

    // All assets must be in https.
    // Force it to use https.
    if (substr($url, 0, 2) === "//") {
      $url = ltrim($url, '//');
      $url = 'https://' . $url;
    }
    elseif (substr($url, 0, 7) === "http://") {
      $url = ltrim($url, 'http://');
      $url = 'https://' . $url;
    }

    try {
      $info = $this->followRedirects($url);
      $asset->setFoundUrl($info['url'])
        ->setHttpCode($info['http_code'])
        ->setNumberOfRedirects($this->redirectCount);
    }
    catch (ResourceFailException $e) {
      $asset->setNumberOfRedirects($this->redirectCount)
        ->setError($e->getMessage(), $e->getCode());

      // If the request timed out,
      // throw a RequestTimeoutException so the processor can give up for this process.
      if ($e->getCode() == CURLE_OPERATION_TIMEDOUT || $e->getCode() == CURLOPT_TIMEOUT) {
        throw new RequestTimeoutException($e->getMessage(), $e->getCode());
      }
    }

    return $asset;
  }

}
