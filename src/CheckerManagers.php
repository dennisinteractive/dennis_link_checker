<?php

namespace Drupal\dennis_link_checker;

use Drupal\Core\Path\AliasManager;
use Drupal\redirect\RedirectRepository;
use Drupal\Core\Language\LanguageManager;

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
   * @var CheckerQueriesManager
   */
  protected $checker_queries_manager;

  /**
   * CheckerManagers constructor.
   *
   * @param AliasManager $aliasManager
   * @param LanguageManager $languageManager
   * @param RedirectRepository $redirectRepository
   * @param CheckerQueriesManager $checkerQueriesManager
   */
  public function __construct(AliasManager $aliasManager,
                              LanguageManager $languageManager,
                              RedirectRepository $redirectRepository,
                              CheckerQueriesManager $checkerQueriesManager) {
    $this->alias_manger = $aliasManager;
    $this->language_manger = $languageManager;
    $this->redirect_repository = $redirectRepository;
    $this->checker_queries_manager = $checkerQueriesManager;
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

  /**
   * @return CheckerQueriesManager
   */
  public function getCheckerQueriesManager() {
    return  $this->checker_queries_manager;
  }
}
