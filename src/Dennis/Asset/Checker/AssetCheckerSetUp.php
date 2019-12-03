<?php

namespace Drupal\dennis_link_checker\Dennis\Asset\Checker;

use Drupal\Core\State\State;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Queue;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Config;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Logger;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Database;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Throttler;
use Drupal\dennis_link_checker\Dennis\Link\Checker\EntityHandler;
use Drupal\dennis_link_checker\Dennis\Link\Checker\LinkLocalisation;


/**
 * Class LinkCheckerSetUp
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class AssetCheckerSetUp implements AssetCheckerSetUpInterface {

  /***
   * @var Connection
   */
  protected $connection;

  /**
   * @var State
   */
  protected $state;

  /**
   * @var RequestStack
   */
  protected $request;

  /**
   * LinkCheckerSetUp constructor.
   *
   * @param RequestStack $request
   * @param Connection $connection
   * @param State $state
   */
  public function __construct(RequestStack $request, Connection $connection, State $state) {
    $this->connection = $connection;
    $this->state = $state;
    $this->request = $request;
  }

  /**
   *
   * @param array $nids
   * @return Processor
   */
  public function run(array $nids) {
    $site_host = $this->request->getCurrentRequest()->getSchemeAndHttpHost();
    $config = (new Config())
      ->setLogger((new Logger())->setVerbosity(Logger::VERBOSITY_HIGH))
      ->setSiteHost($site_host)
      ->setMaxRedirects(10)
      ->setInternalOnly(TRUE)
      ->setLocalisation(LinkLocalisation::ORIGINAL)
      ->setFieldNames($this->state->get('dennis_link_checker_fields', ['body']))
      ->setNodeList($nids);

    $queue = new Queue('dennis_asset_checker', $this->connection);
    $entity_handler = new EntityHandler($config, $this->connection);
    // Make sure we don't request more than one page per second.
    $curl_throttler = new Throttler(1);
    // Database object that allows interaction with the DB.
    $database = new Database($this->connection);
    $analyzer = new AssetAnalyser($config, $curl_throttler, $database);
    $processor = new AssetProcessor($config, $queue, $entity_handler, $analyzer, $this->connection, $this->state);
    $processor->run();
  }
}












