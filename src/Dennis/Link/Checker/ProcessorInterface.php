<?php
/**
 * @file ProcessorInterface
 */
namespace Dennis\Link\Checker;
use DrupalReliableQueueInterface;

/**
 * Interface ProcessorInterface
 * @package Dennis\Link\Checker
 */
interface ProcessorInterface {

  /**
   * ProcessorInterface constructor.
   *
   * @param $queue
   *  The drupal queue.
   * @param int $time_limit
   *  Maximim number of seconds to allow the processor to run.
   */
  public function __construct(DrupalReliableQueueInterface $queue, $time_limit = 1800);

  /**
   * Processes the queue.
   *
   *
   * @return ProcessorInterface
   */
  public function run();

  /**
   * The drupal queue object.
   * @return DrupalReliableQueueInterface
   */
  public function getQueue();

  /**
   * Finds & adds items to the queue.
   * @return ProcessorInterface
   */
  public function enqueue();

  /**
   * Add an item to the queue.
   *
   * @param ItemInterface $item
   * @return ProcessorInterface
   */
  public function addItem(ItemInterface $item);

  /**
   * The number of items in the queue.
   *
   * @return boolean
   */
  public function numberOfItems();


  /**
   * Process the next item.
   *
   * @return ProcessorInterface
   */
  public function doNextItem();

  /**
   * The next item to be processed.
   * @return \stdClass
   */
  public function getQueueItem();

  /**
   * The textarea strings for the entity.
   * @param ItemInterface $item
   * @return array
   */
  public function getTexts(ItemInterface $item);

  /**
   * Find links in the text.
   * @param $text
   * @return array
   */
  public function findLinks($text);

  /**
   * Checks and changes the url to be correct.
   * @param $url
   * @return string
   */
  public function checkLink($url);

  /**
   * Report on links that had too many redirects.
   * @return array
   */
  public function detectedExcessiveRedirects();

}
