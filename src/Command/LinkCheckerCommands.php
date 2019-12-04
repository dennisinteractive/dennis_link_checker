<?php

namespace Drupal\dennis_link_checker\Command;

use Drupal\Core\State\State;
use Drush\Commands\DrushCommands;
use Drupal\Core\Database\Connection;
use Drupal\dennis_link_checker\Dennis\CheckerManagers;
use Drupal\dennis_link_checker\Dennis\Link\Checker\LinkCheckerSetUp;
use Drupal\dennis_link_checker\Dennis\Asset\Checker\AssetCheckerSetUp;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * A Drush command file for interacting with the Write API.
 */
class LinkCheckerCommands extends DrushCommands {

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * @var RequestStack
   */
  protected $request;

  /**
   * The Key/Value Store to use for state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * @var CheckerManagers
   */
  protected $checker_managers;

  /**
   * LinkCheckerCommands constructor.
   *
   * @param Connection $connection
   * @param RequestStack $request
   * @param State $state
   * @param CheckerManagers $checkerManagers
   */
  public function __construct(
    Connection $connection,
    RequestStack $request,
    State $state,
    CheckerManagers $checkerManagers) {
    $this->connection = $connection;
    $this->request = $request;
    $this->state = $state;
    $this->checker_managers = $checkerManagers;
  }

  /**
   * Link Checker
   *
   * @param string $nid
   *   Type of node to update
   *   Argument provided to the drush command.
   *
   * @command link-checker:link
   *
   * @usage link-checker:link
   * @usage link-checker:link '1,2,3'
   * @throws \Exception
   */
  public function link($nid = '') {
    $this->output()->writeln('Starting drush link-checker:link: ' . date(DATE_RFC2822));
    $nids = !empty($nid) ? explode(',', $nid) : [];
    $set_up = new LinkCheckerSetUp(
      $this->request,
      $this->connection,
      $this->state,
      $this->checker_managers
    );
    $set_up->run($nids);
    $this->output()->writeln('Finished drush link-checker:link: ' . date(DATE_RFC2822));
  }

  /**
   * Asset Checker
   *
   * @param string $nid
   *   Type of node to update
   *   Argument provided to the drush command.
   *
   * @command link-checker:asset
   *
   * @usage link-checker:asset
   * @usage link-checker:asset '1,2,3'
   * @throws \Exception
   */
  public function asset($nid = '') {
    $this->output()->writeln('Starting drush link-checker:asset: ' . date(DATE_RFC2822));
    $nids = !empty($nid) ? explode(',', $nid) : [];
    $set_up = new AssetCheckerSetUp(
      $this->request,
      $this->connection,
      $this->state,
      $this->checker_managers
    );
    $set_up->run($nids);
    $this->output()->writeln('Finished drush link-checker:asset: ' . date(DATE_RFC2822));
  }
}
