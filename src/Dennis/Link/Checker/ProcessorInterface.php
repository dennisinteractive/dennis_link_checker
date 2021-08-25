<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Core\Queue\ReliableQueueInterface;

/**
 * Interface ProcessorInterface.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
interface ProcessorInterface {

  /**
   * Sets how long in seconds the processor is allowed to run.
   *
   * @param int $time_limit
   *   Maximim number of seconds to allow the processor to run.
   *
   * @return ProcessorInterface
   *   Returns the processor interface.
   */
  public function setTimeLimit($time_limit);

  /**
   * How the link should be changed for local links, if at all.
   *
   * @param mixed $localisation
   *   The localisation.
   *
   * @return ProcessorInterface
   *   Returns the processor interface.
   */
  public function setLocalisation($localisation);

  /**
   * How the link should be changed for local links, if at all.
   *
   * @return string
   *   Returns the localisation string.
   */
  public function localisation();

  /**
   * Maximum number of seconds to allow the processor to run.
   *
   * @return int
   *   Returns the time limit.
   */
  public function getTimeLimit();

  /**
   * Processes the queue.
   *
   * @return bool
   *   Returns FALSE if in maintenance mode, else TRUE.
   * @throws TimeoutException
   */
  public function run();

  /**
   * The configuration object.
   *
   * @param ConfigInterface $config
   *   Config Interface.
   *
   * @return self
   *   Returns this.
   */
  public function setConfig(ConfigInterface $config);

  /**
   * The queue to process.
   *
   * @param \Drupal\Core\Queue\ReliableQueueInterface $queue
   *   Reliable Queue Interface.
   *
   * @return ProcessorInterface
   *   Returns the processor interface.
   */
  public function setQueue(ReliableQueueInterface $queue);

  /**
   * The drupal queue object.
   *
   * @return \Drupal\Core\Queue\ReliableQueueInterface
   *   Returns the Reliable queue interface.
   */
  public function getQueue();

  /**
   * The object that understands entities.
   *
   * @param EntityHandlerInterface $entity_handler
   *   Entity handler.
   *
   * @return ProcessorInterface
   *   Returns the processor interface.
   */
  public function setEntityHandler(EntityHandlerInterface $entity_handler);

  /**
   * Get entity handler.
   *
   * @return EntityHandlerInterface
   *   Returns the entity handler interface.
   */
  public function getEntityHandler();

  /**
   * The object that does the actual fixing of the link.
   *
   * @param AnalyzerInterface $analyzer
   *   Analyzer Interface.
   *
   * @return ProcessorInterface
   *   Returns the processor interface.
   */
  public function setAnalyzer(AnalyzerInterface $analyzer);

  /**
   * Get Analyzer.
   *
   * @return AnalyzerInterface
   *   Returns the Analyzer interface.
   */
  public function getAnalyzer();

  /**
   * Finds & adds items to the queue.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return ProcessorInterface
   *   Returns the processor interface.
   */
  public function enqueue($field_name);

  /**
   * Ensures there is always something in the queue.
   */
  public function ensureEnqueued();

  /**
   * Add an item to the queue.
   *
   * @param ItemInterface $item
   *   The item interface.
   *
   * @return ProcessorInterface
   *   Returns the processor interface.
   */
  public function addItem(ItemInterface $item);

  /**
   * The number of items in the queue.
   */
  public function numberOfItems();

  /**
   * Process the next item.
   * @throws RequestTimeoutException
   * @throws TimeoutException
   */
  public function doNextItem();

  /**
   * Process a single queue item's data.
   *
   * Can be used by callback_queue_worker($queue_item_data)
   *
   * @param mixed $item
   *   The data that was passed to DrupalQueueInterface::createItem() when the item was queued.
   */
  public function queueWorker($item);

  /**
   * The next item to be processed.
   *
   * @return object
   *   Returns the queue item.
   */
  public function getQueueItem();

  /**
   * Checks and changes the url to be correct.
   *
   * @param ItemInterface $item
   *   The item interface.
   * @param FieldInterface $field
   *   The field interface.
   *
   * @return array
   *   Returns an array of correct links.
   */
  public function correctLinks(ItemInterface $item, FieldInterface $field);

}
