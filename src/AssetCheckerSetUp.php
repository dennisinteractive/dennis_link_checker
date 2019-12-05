<?php

namespace Drupal\dennis_link_checker;

use Drupal\dennis_link_checker\Dennis\Link\Checker\Queue;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Config;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Logger;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Database;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Throttler;
use Drupal\dennis_link_checker\Dennis\Link\Checker\EntityHandler;
use Drupal\dennis_link_checker\Dennis\Link\Checker\LinkLocalisation;
use Drupal\dennis_link_checker\Dennis\Asset\Checker\AssetAnalyser;
use Drupal\dennis_link_checker\Dennis\Asset\Checker\AssetProcessor;


/**
 * Class LinkCheckerSetUp
 *
 * @package Drupal\dennis_link_checker
 */
class AssetCheckerSetUp extends LinkCheckerSetUp implements AssetCheckerSetUpInterface {

  /**
   * @inheritDoc
   */
  public function run(array $nids) {
    $this->setUp($nids)->run();
  }

  /**
   * @inheritDoc
   */
  public function setUp ($nids) {
    $config = (new Config())
      ->setLogger((new Logger())->setVerbosity(Logger::VERBOSITY_HIGH))
      ->setSiteHost($this->siteUrl())
      ->setMaxRedirects(10)
      ->setInternalOnly(TRUE)
      ->setLocalisation(LinkLocalisation::ORIGINAL)
      ->setFieldNames($this->state->get('dennis_link_checker_fields', ['body']))
      ->setNodeList($nids);

    $queue = new Queue('dennis_asset_checker', $this->connection);
    $entity_handler = new EntityHandler(
      $config,
      $this->connection,
      $this->checker_managers
    );
    // Make sure we don't request more than one page per second.
    $curl_throttler = new Throttler(1);
    // Database object that allows interaction with the DB.
    $database = new Database($this->connection);
    $analyzer = new AssetAnalyser($config, $curl_throttler, $database);

    return new AssetProcessor(
      $config,
      $queue,
      $entity_handler,
      $analyzer,
      $this->connection,
      $this->checker_managers,
      $this->state
    );
  }
}
