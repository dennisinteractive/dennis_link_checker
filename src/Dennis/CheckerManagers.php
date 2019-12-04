<?php

namespace Drupal\dennis_link_checker\Dennis;

use Drupal\Core\Path\AliasManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Entity\EntityTypeManager;


/**
 * Class CheckerManagers
 *
 * @package Drupal\dennis_link_checker\Dennis
 */
class CheckerManagers implements CheckerManagersInterface {

  /**
   * @var AliasManager
   */
  protected $alias_manger;

  /**
   * @var LanguageManager
   */
  protected $language_manger;

  /**
   * @var EntityTypeManager
   */
  protected $entity_type_manager;

  /**
   * @var ModuleHandler
   */
  protected $module_handler;

  /**
   * @var ConfigFactory
   */
  protected $config_factory;


  /**
   * CheckerManagers constructor.
   *
   * @param EntityTypeManager $entityTypeManager
   * @param AliasManager $aliasManager
   * @param LanguageManager $languageManager
   * @param ModuleHandler $moduleHandler
   * @param ConfigFactory $configFactory
   */
  public function __construct(EntityTypeManager $entityTypeManager,
                              AliasManager $aliasManager,
                              LanguageManager $languageManager,
                              ModuleHandler $moduleHandler,
                              ConfigFactory $configFactory) {
    $this->alias_manger = $aliasManager;
    $this->language_manger = $languageManager;
    $this->entity_type_manager = $entityTypeManager;
    $this->module_handler = $moduleHandler;
    $this->config_factory = $configFactory;
  }

  /**
   * @return AliasManager
   */
  public function getAliasManager() {
    return $this->alias_manger;
  }

  /**
   * @return LanguageManager
   */
  public function getLanguageManager() {
    return $this->language_manger;
  }

  /**
   * @return EntityTypeManager
   */
  public function getEntityTypeManager() {
    return $this->entity_type_manager;
  }

  /**
   * @return ModuleHandler
   */
  public function getModuleHandler() {
    return $this->module_handler;
  }

  /**
   * @return ConfigFactory
   */
  public function getConfigFactory() {
    return $this->config_factory;
  }

}
