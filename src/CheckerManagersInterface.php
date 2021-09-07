<?php

namespace Drupal\dennis_link_checker;

use Drupal\Core\Path\AliasManager;
use Drupal\redirect\RedirectRepository;
use Drupal\Core\Language\LanguageManager;

/**
 * Interface CheckerManagersInterface.
 *
 * @package Drupal\dennis_link_checker
 */
interface CheckerManagersInterface {

  /**
   * Get the alias manager.
   *
   * @return \Drupal\Core\Path\AliasManager
   *   Returns alias manager.
   */
  public function getAliasManager();

  /**
   * Get the language manager.
   *
   * @return \Drupal\Core\Language\LanguageManager
   *   Returns the language manager.
   */
  public function getLanguageManager();

  /**
   * Get the redirect repository.
   *
   * @return \Drupal\redirect\RedirectRepository
   *   Returns the redirect repository.
   */
  public function getRedirectRepository();

}
