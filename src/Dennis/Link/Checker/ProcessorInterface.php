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
   *
   */
  public function __construct(DrupalReliableQueueInterface $queue, EntityHandlerInterface $entity_handler, Corrector $corrector);

  /**
   * Sets how long in seconds the processor is allowed to run.
   *
   * @param int $time_limit
   *  Maximim number of seconds to allow the processor to run.
   * @return ProcessorInterface
   */
  public function setTimeLimit($time_limit);

  /**
   * Maximum number of seconds to allow the processor to run.
   *
   * @return integer
   */
  public function getTimeLimit();

  /**
   * Processes the queue.
   *
   * @return ProcessorInterface
   */
  public function run();

  /**
   * The queue to process.
   * @return ProcessorInterface
   */
  public function setQueue(DrupalReliableQueueInterface $queue);

  /**
   * The drupal queue object.
   * @return DrupalReliableQueueInterface
   */
  public function getQueue();

  /**
   * The object that understands entities.
   *
   * @param EntityHandlerInterface $entityHandler
   * @return ProcessorInterface
   */
  public function setEntityHandler(EntityHandlerInterface $entity_handler);

  /**
   * @return EntityHandlerInterface
   */
  public function getEntityHandler();

  /**
   * The object that does the actual fixing of the link.
   *
   * @param CorrectorInterface $corrector
   * @return ProcessorInterface
   */
  public function setCorrector(CorrectorInterface $corrector);

  /**
   * @return CorrectorInterface
   */
  public function getCorrector();

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
   * Find links in the text.
   * @param ItemInterface $item
   * @return array
   */
  public function findLinks(ItemInterface $item);

  /**
   * Checks and changes the url to be correct.
   *
   * @param ItemInterface $item
   * @param array $links
   * @return array
   */
  public function correctLinks(ItemInterface $item, $links);

  /**
   * Report on links that had too many redirects.
   * @return array
   */
  public function detectedExcessiveRedirects();

}
