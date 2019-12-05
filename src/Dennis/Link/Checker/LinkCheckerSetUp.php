<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Core\State\State;
use Drupal\Core\Database\Connection;
use Drupal\dennis_link_checker\Dennis\CheckerManagers;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * Class LinkCheckerSetUp
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
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
    $set_internal = TRUE;
    if ($this->state->get('dennis_link_checker_link_internal', 1) == 0) {
      $set_internal = FALSE;
    }
    $config = (new Config())
      ->setLogger((new Logger())->setVerbosity(Logger::VERBOSITY_HIGH))
      ->setSiteHost($site_host)
      ->setMaxRedirects(10)
      ->setInternalOnly($set_internal)
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

    $processor = new Processor(
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
