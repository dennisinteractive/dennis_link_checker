<?php

namespace Drupal\dennis_link_checker\Command;

use Drush\Commands\DrushCommands;
use Drupal\dennis_link_checker\LinkCheckerSetUp;
use Drupal\dennis_link_checker\AssetCheckerSetUp;

/**
 * A Drush command file for interacting with the Write API.
 */
class LinkCheckerCommands extends DrushCommands {

  /**
   * Link checker set up.
   *
   * @var \Drupal\dennis_link_checker\LinkCheckerSetUp
   */
  protected $linkChecker;

  /**
   * Asset checker Set up.
   *
   * @var \Drupal\dennis_link_checker\AssetCheckerSetUp
   */
  protected $assetChecker;

  /**
   * LinkCheckerCommands constructor.
   *
   * @param \Drupal\dennis_link_checker\LinkCheckerSetUp $linkCheckerSetUp
   *   Link checker set up.
   * @param \Drupal\dennis_link_checker\AssetCheckerSetUp $assetCheckerSetUp
   *   Asset checker Set up.
   */
  public function __construct(
    LinkCheckerSetUp $linkCheckerSetUp,
    AssetCheckerSetUp $assetCheckerSetUp
  ) {
    $this->linkChecker = $linkCheckerSetUp;
    $this->assetChecker = $assetCheckerSetUp;
  }

  /**
   * Link Checker.
   *
   * @param string $nid
   *   Type of node to update
   *   Argument provided to the drush command.
   *
   * @command link-checker:link
   *
   * @usage link-checker:link
   * @usage link-checker:link '1,2,3'
   *
   * @throws \Exception
   */
  public function link($nid = '') {
    $this->output()->writeln('Starting drush link-checker:link: ' . date(DATE_RFC2822));
    $nids = !empty($nid) ? explode(',', $nid) : [];
    $this->linkChecker->run($nids);
    $this->output()->writeln('Finished drush link-checker:link: ' . date(DATE_RFC2822));
  }

  /**
   * Asset Checker.
   *
   * @param string $nid
   *   Type of node to update
   *   Argument provided to the drush command.
   *
   * @command link-checker:asset
   *
   * @usage link-checker:asset
   * @usage link-checker:asset '1,2,3'
   *
   * @throws \Exception
   */
  public function asset($nid = '') {
    $this->output()->writeln('Starting drush link-checker:asset: ' . date(DATE_RFC2822));
    $nids = !empty($nid) ? explode(',', $nid) : [];
    $this->assetChecker->run($nids);
    $this->output()->writeln('Finished drush link-checker:asset: ' . date(DATE_RFC2822));
  }

}
