<?php

namespace Drupal\dennis_link_checker;

use Drupal\path_alias\AliasManagerInterface;
use Drupal\redirect\RedirectRepository;
use Drupal\Core\Language\LanguageManager;

/**
 * Class CheckerManagers.
 *
 * @package Drupal\dennis_link_checker\Dennis
 */
class CheckerManagers implements CheckerManagersInterface {

  /**
   * Alias manager interface.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Redirect repository.
   *
   * @var \Drupal\redirect\RedirectRepository
   */
  protected $redirectRepository;

  /**
   * Checker queries manager.
   *
   * @var CheckerQueriesManager
   */
  protected $checkerQueriesManager;

  /**
   * CheckerManagers constructor.
   *
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   Alias manager interface.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   Language manager.
   * @param \Drupal\redirect\RedirectRepository $redirectRepository
   *   Redirect repository.
   * @param CheckerQueriesManager $checkerQueriesManager
   *   Checker queries manager.
   */
  public function __construct(AliasManagerInterface $aliasManager,
                              LanguageManager $languageManager,
                              RedirectRepository $redirectRepository,
                              CheckerQueriesManager $checkerQueriesManager) {
    $this->aliasManager = $aliasManager;
    $this->languageManager = $languageManager;
    $this->redirectRepository = $redirectRepository;
    $this->checkerQueriesManager = $checkerQueriesManager;
  }

  /**
   * {@inheritDoc}
   */
  public function getAliasManager() {
    return $this->aliasManager;
  }

  /**
   * {@inheritDoc}
   */
  public function getLanguageManager() {
    return $this->languageManager;
  }

  /**
   * {@inheritDoc}
   */
  public function getRedirectRepository() {
    return $this->redirectRepository;
  }

  /**
   * Get checker queries manager.
   *
   * @return CheckerQueriesManager
   *   Returns the checker queries manager.
   */
  public function getCheckerQueriesManager() {
    return $this->checkerQueriesManager;
  }

}
