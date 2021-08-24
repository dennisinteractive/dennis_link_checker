<?php

namespace Drupal\dennis_link_checker;

/**
 * Interface LinkCheckerSetUpInterface.
 *
 * @package Drupal\dennis_link_checker
 */
interface LinkCheckerSetUpInterface {

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
   * @return \Drupal\dennis_link_checker\Dennis\Link\Checker\Processor
   *   Link checker processor.
   */
  public function setUp(array $nids);

}
