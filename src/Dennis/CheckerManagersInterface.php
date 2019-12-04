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
   * @return EntityTypeManager
   */
  public function getEntityTypeManager();

  /**
   * @return ModuleHandler
   */
  public function getModuleHandler();

  /**
   * @return ConfigFactory
   */
  public function getConfigFactory();

}
