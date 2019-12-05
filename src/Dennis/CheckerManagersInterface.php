<?php


namespace Drupal\dennis_link_checker\Dennis;


interface CheckerManagersInterface {

  /**
   * @return AliasManager
   */
  public function getAliasManager();

  /**
   * @return LanguageManager
   */
  public function getLanguageManager();

  /**
   * @return ConfigFactory
   */
  public function getRedirectRepository();

}
