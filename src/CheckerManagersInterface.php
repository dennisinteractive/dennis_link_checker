<?php

namespace Drupal\dennis_link_checker;

use Drupal\Core\Path\AliasManager;
use Drupal\redirect\RedirectRepository;
use Drupal\Core\Language\LanguageManager;


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
   * @return RedirectRepository
   */
  public function getRedirectRepository();

}
