<?php

namespace Drupal\dennis_link_checker;

/**
 * Interface AssetCheckerSetUpInterface.
 *
 * @package Drupal\dennis_link_checker
 */
interface AssetCheckerSetUpInterface extends LinkCheckerSetUpInterface {

  /**
   * Run the asset Checker processor.
   *
   * @param array $nids
   *   Array of nids.
   */
  public function run(array $nids);

  /**
   * Set up the Asset checker processor.
   *
   * @param array $nids
   *   Array of nids.
   *
   * @return \Drupal\dennis_link_checker\Dennis\Asset\Checker\AssetProcessor
   *   Returns asset processor.
   */
  public function setUp(array $nids);

}
