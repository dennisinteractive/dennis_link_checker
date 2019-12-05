<?php

namespace Drupal\dennis_link_checker;

use Drupal\dennis_link_checker\Dennis\Asset\Checker\AssetProcessor;

interface AssetCheckerSetUpInterface extends LinkCheckerSetUpInterface {

  /**
   * Run the asset Checker processor.
   *
   * @param array $nids
   */
  public function run(array $nids);

  /**
   * Set up the Asset checker processor.
   *
   * @param $nids
   * @return AssetProcessor
   */
  public function setUp($nids);
}
