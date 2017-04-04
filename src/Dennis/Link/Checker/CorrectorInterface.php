<?php
/**
 * @file ItemInterface
 */
namespace Dennis\Link\Checker;

/**
 * Interface CorrectorInterface
 * @package Dennis\Link\Checker
 */
interface CorrectorInterface {

  /**
   * Corrects the link.
   *
   * @param $link string
   * @return string
   */
  public function link($link);

  /**
   * Corrects an array of links.
   *
   * @param $links
   * @return array
   */
  public function multipleLinks($links);

}
