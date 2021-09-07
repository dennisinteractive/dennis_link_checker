<?php

namespace Drupal\dennis_link_checker;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\State;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Config;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Logger;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Database;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Analyzer;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Throttler;
use Drupal\dennis_link_checker\Dennis\Link\Checker\Processor;
use Drupal\dennis_link_checker\Dennis\Link\Checker\EntityHandler;
use Drupal\dennis_link_checker\Dennis\Link\Checker\LinkLocalisation;

/**
 * Class LinkCheckerSetUp.
 *
 * @package Drupal\dennis_link_checker
 */
class LinkCheckerSetUp implements LinkCheckerSetUpInterface {

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /***
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * State.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * Checker managers.
   *
   * @var CheckerManagers
   */
  protected $checkerManagers;

  /**
   * Logger channel factory interface.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Config factory interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * LinkCheckerSetUp constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Request stack.
   * @param \Drupal\Core\State\State $state
   *   State.
   * @param \Drupal\dennis_link_checker\CheckerManagers $checkerManagers
   *   Check managers.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   */
  public function __construct(Connection $connection,
                              RequestStack $request,
                              State $state,
                              CheckerManagers $checkerManagers,
                              LoggerChannelFactoryInterface $loggerFactory,
                              ConfigFactoryInterface $configFactory) {
    $this->connection = $connection;
    $this->request = $request;
    $this->state = $state;
    $this->checkerManagers = $checkerManagers;
    $this->loggerFactory = $loggerFactory;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritDoc}
   */
  public function run(array $nids) {
    $this->setUp($nids)->run();
  }

  /**
   * {@inheritDoc}
   */
  public function setUp(array $nids) {
    $config = (new Config())
      ->setLogger((new Logger($this->loggerFactory))->setVerbosity(Logger::VERBOSITY_HIGH))
      ->setSiteHost($this->siteUrl())
      ->setMaxRedirects(10)
      ->setInternalOnly(TRUE)
      ->setLocalisation(LinkLocalisation::ORIGINAL)
      ->setFieldNames($this->state->get('dennis_link_checker_fields', ['body']))
      ->setNodeList($nids);

    $queue = new Queue('dennis_link_checker', $this->connection);
    $entity_handler = new EntityHandler(
      $config,
      $this->checkerManagers
    );
    // Make sure we don't request more than one page per second.
    $curl_throttler = new Throttler(1);
    // Database object that allows interaction with the DB.
    $database = new Database();
    $analyzer = new Analyzer($config, $curl_throttler, $database);

    return new Processor(
      $config,
      $queue,
      $entity_handler,
      $analyzer,
      $this->checkerManagers,
      $this->state
    );
  }

  /**
   * Return the configurable site url for checking.
   *
   * @return mixed|null
   *   Return the site url.
   */
  protected function siteUrl() {
    return $this->configFactory->getEditable('dennis_link_checker.settings')->get('link_checker_site_url');
  }

}
