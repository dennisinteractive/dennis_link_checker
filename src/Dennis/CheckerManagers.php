<?php

namespace Drupal\dennis_link_checker\Dennis;

use Drupal\Core\Path\AliasManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\redirect\RedirectRepository;


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
   * @var RedirectRepository
   */
  protected $redirect_repository;

  /**
   * CheckerManagers constructor.
   *
   * @param AliasManager $aliasManager
   * @param LanguageManager $languageManager
   * @param RedirectRepository $redirectRepository
   */
  public function __construct(AliasManager $aliasManager,
                              LanguageManager $languageManager,
                              RedirectRepository $redirectRepository) {
    $this->alias_manger = $aliasManager;
    $this->language_manger = $languageManager;
    $this->redirect_repository = $redirectRepository;
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
   * @return RedirectRepository
   */
  public function getRedirectRepository() {
    return $this->redirect_repository;
  }

}
