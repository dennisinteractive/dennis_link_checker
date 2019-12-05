<?php

namespace Drupal\dennis_link_checker;

use Drupal\Core\State\State;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Queue;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Config;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Logger;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Database;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Analyzer;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Throttler;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Processor;
use Drupal\dennis_link_checker\Dennis\Link\Checker\EntityHandler;
use Drupal\dennis_link_checker\Dennis\Link\Checker\LinkLocalisation;


/**
 * Class LinkCheckerSetUp
 *
 * @package Drupal\dennis_link_checker
 */
class LinkCheckerSetUp implements LinkCheckerSetUpInterface {

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
   * LinkCheckerSetUp constructor.
   *
   * @param Connection $connection
   * @param RequestStack $request
   * @param State $state
   * @param CheckerManagers $checkerManagers
   */
  public function __construct(Connection $connection,
                              RequestStack $request,
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
    $this->setUp($nids)->run();
  }

  /**
   * @param $nids
   * @return Processor
   */
  public function setUp($nids) {
    $config = (new Config())
      ->setLogger((new Logger())->setVerbosity(Logger::VERBOSITY_HIGH))
      ->setSiteHost($this->siteUrl())
      ->setMaxRedirects(10)
      ->setInternalOnly(TRUE)
      ->setLocalisation(LinkLocalisation::ORIGINAL)
      ->setFieldNames($this->state->get('dennis_link_checker_fields', ['body']))
      ->setNodeList($nids);

    $queue = new Queue('dennis_link_checker', $this->connection);
    $entity_handler = new EntityHandler(
      $config,
      $this->connection,
      $this->checker_managers
    );
    // Make sure we don't request more than one page per second.
    $curl_throttler = new Throttler(1);
    // Database object that allows interaction with the DB.
    $database = new Database($this->connection);
    $analyzer = new Analyzer($config, $curl_throttler, $database);

    return new Processor(
      $config,
      $queue,
      $entity_handler,
      $analyzer,
      $this->connection,
      $this->checker_managers,
      $this->state
    );
  }

  /**
   * Return the configurable site url for checking.
   * @return mixed|null
   */
  protected function siteUrl() {
    $default_site_url = $this->request->getCurrentRequest()->getHttpHost();
    return $this->state->get('dennis_link_checker_site_url', $default_site_url);
  }

}
