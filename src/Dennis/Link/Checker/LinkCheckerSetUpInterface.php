<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;


interface LinkCheckerSetUpInterface {

  /**
   *
   * @param array $nids
   * @return Processor
   */
  public function run(array $nids);
}
