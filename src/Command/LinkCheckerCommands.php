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
   * @var LinkCheckerSetUp
   */
  protected $link_checker;

  /**
   * @var AssetCheckerSetUp
   */
  protected $asset_checker;


  /**
   * LinkCheckerCommands constructor.
   *
   * @param LinkCheckerSetUp $linkCheckerSetUp
   * @param AssetCheckerSetUp $assetCheckerSetUp
   */
  public function __construct(
    LinkCheckerSetUp $linkCheckerSetUp,
    AssetCheckerSetUp $assetCheckerSetUp
  ) {
    $this->link_checker = $linkCheckerSetUp;
    $this->asset_checker = $assetCheckerSetUp;
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
    $this->link_checker->run($nids);
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
    $this->asset_checker->run($nids);
    $this->output()->writeln('Finished drush link-checker:asset: ' . date(DATE_RFC2822));
  }
}
