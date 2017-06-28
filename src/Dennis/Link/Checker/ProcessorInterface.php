<?php
/**
 * @file
 * ProcessorInterface
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
   * @param ConfigInterface $config
   * @param DrupalReliableQueueInterface $queue
   * @param EntityHandlerInterface $entity_handler
   * @param AnalyzerInterface $analyzer
   * @param array $fields
   */
  public function __construct(ConfigInterface $config, DrupalReliableQueueInterface $queue, EntityHandlerInterface $entity_handler, AnalyzerInterface $analyzer, $fields);

  /**
   * Sets how long in seconds the processor is allowed to run.
   *
   * @param int $time_limit
   *  Maximim number of seconds to allow the processor to run.
   * @return ProcessorInterface
   */
  public function setTimeLimit($time_limit);

  /**
   * How the link should be changed for local links, if at all
   *
   * @param $localisation
   * @return ProcessorInterface
   */
  public function setLocalisation($localisation);

  /**
   * How the link should be changed for local links, if at all
   *
   * @return string
   */
  public function localisation();

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
   *
   * @param DrupalReliableQueueInterface $queue
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
   * @param EntityHandlerInterface $entity_handler
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
   * @param AnalyzerInterface $analyzer
   * @return ProcessorInterface
   */
  public function setAnalyzer(AnalyzerInterface $analyzer);

  /**
   * @return AnalyzerInterface
   */
  public function getAnalyzer();

  /**
   * Finds & adds items to the queue.
   *
   * @param string $field_name
   * @return ProcessorInterface
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
   * Process a single queue item's data.
   *
   * Can be used by callback_queue_worker($queue_item_data)
   *
   * @param Item $item
   *  The data that was passed to DrupalQueueInterface::createItem() when the item was queued.
   */
  public function queueWorker($item);

  /**
   * The next item to be processed.
   * @return \stdClass
   */
  public function getQueueItem();

  /**
   * Checks and changes the url to be correct.
   *
   * @param ItemInterface $item
   * @param Field $field
   * @return array
   */
  public function correctLinks(ItemInterface $item, Field $field);

}
