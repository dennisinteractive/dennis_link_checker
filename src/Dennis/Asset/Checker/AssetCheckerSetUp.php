<?php

namespace Drupal\dennis_link_checker\Dennis\Asset\Checker;

use Drupal\Core\State\State;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\dennis_link_checker\Dennis\CheckerManagers;
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

  /**
   * @var RequestStack
   */
  protected $request;

  /***
   * @var Connection
   */
  protected $connection;

  /**
   * @var State
   */
  protected $state;

  /**
   * @var CheckerManagers
   */
  protected $checker_managers;

  /**
   * AssetCheckerSetUp constructor.
   *
   * @param RequestStack $request
   * @param Connection $connection
   * @param State $state
   * @param CheckerManagers $checkerManagers
   */
  public function __construct(RequestStack $request,
                              Connection $connection,
                              State $state,
                              CheckerManagers $checkerManagers) {
    $this->connection = $connection;
    $this->state = $state;
    $this->request = $request;
    $this->checker_managers = $checkerManagers;
  }

  /**
   * @param array $nids
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
    $processor = new AssetProcessor(
      $config,
      $queue,
      $entity_handler,
      $analyzer,
      $this->connection,
      $this->checker_managers,
      $this->state
    );
    $processor->run();
  }
}












