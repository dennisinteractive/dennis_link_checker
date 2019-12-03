<?php

namespace Drupal\dennis_link_checker\Dennis\Asset\Checker;


interface AssetCheckerSetUpInterface {

  /**
   *
   * @param array $nids
   */
  public function run(array $nids);
}
