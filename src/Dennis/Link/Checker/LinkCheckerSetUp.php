<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Core\State\State;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class LinkCheckerSetUp
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class LinkCheckerSetUp implements LinkCheckerSetUpInterface {

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

    $queue = new Queue('dennis_link_checker', $this->connection);
    $entity_handler = new EntityHandler($config, $this->connection);
    // Make sure we don't request more than one page per second.
    $curl_throttler = new Throttler(1);
    // Database object that allows interaction with the DB.
    $database = new Database($this->connection);
    $analyzer = new Analyzer($config, $curl_throttler, $database);
    $processor = new Processor($config, $queue, $entity_handler, $analyzer, $this->connection, $this->state);
    $processor->run();
  }
}
