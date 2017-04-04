<?php
/**
 * @file Processor
 */
namespace Dennis\Link\Checker;
use DrupalReliableQueueInterface;

/**
 * Class Processor
 * @package Dennis\Link\Checker
 */
class Processor implements ProcessorInterface {

  protected $queue;

  protected $entityHandeler;

  protected $corrector;

  protected $timeLimit = 1800;

  /**
   * @inheritDoc
   */
  public function __construct(DrupalReliableQueueInterface $queue, EntityHandlerInterface $entity_handler, Corrector $corrector) {
    $this->setQueue($queue);
    $this->setEntityHandler($entity_handler);
    $this->setCorrector($corrector);
  }

  /**
   * @inheritDoc
   */
  public function run() {
    $end = time() + $this->timeLimit;

    // Check for anything in the queue to process.
    if ($this->numberOfItems() == 0) {
      $this->enqueue();
    }

    $more = TRUE;
    while ($more && time() < $end) {
      $more = $this->doNextItem();
    }

  }

  /**
   * @inheritDoc
   */
  public function getQueue() {
    return $this->queue;
  }

  /**
   * @inheritDoc
   */
  public function setTimeLimit($time_limit) {
    $this->timeLimit = $time_limit;
  }

  /**
   * @inheritDoc
   */
  public function getTimeLimit() {
    return $this->timeLimit;
  }

  /**
   * @inheritDoc
   */
  public function setQueue(DrupalReliableQueueInterface $queue) {
    $this->queue = $queue;
  }

  /**
   * @inheritDoc
   */
  public function setEntityHandler(EntityHandlerInterface $entity_handler) {
    $this->entityHandeler = $entity_handler;
  }

  /**
   * @inheritDoc
   */
  public function setCorrector(CorrectorInterface $corrector) {
    $this->corrector = $corrector;
  }

  /**
   * @inheritDoc
   */
  public function getEntityHandler() {
    return $this->entityHandeler;
  }

  /**
   * @inheritDoc
   */
  public function getCorrector() {
    return $this->corrector;
  }

  /**
   * @inheritDoc
   */
  public function enqueue() {
    // entities that have a text area field with a link.

    /*
     * SELECT * FROM field_config WHERE type IN ('text_long', 'text_with_summary')
     */


//    $query = db_select('node', 'n');
//    $query->addField('n', 'nid');
//    // Left join each tabel that could have html links.
//    $query->leftJoin('field_data_body', 'body', "n.nid = body.entity_id");



    // TODO: Implement enqueue() method.

    // TEMPORARY!!
    $this->addItem(new Item('node', 123));
    $this->addItem(new Item('node', 456));

  }

  /**
   * @inheritDoc
   */
  public function addItem(ItemInterface $item) {
    // Add the item to the queue
    return $this->queue->createItem($item);
  }

  /**
   * @inheritDoc
   */
  public function numberOfItems() {
    return $this->queue->numberOfItems();
  }

  /**
   * @inheritDoc
   */
  public function doNextItem() {
    if (!$queue_item = $this->getQueueItem()) {
      return FALSE;
    }

    $item = $queue_item->data;
    $links = $this->findLinks($item);
    $this->correctLinks($links);

    // Remove it from the queue.
    $this->queue->deleteItem($queue_item);
    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function getQueueItem() {
    // Claim item from queue
    if ($queue_item = $this->queue->claimItem()) {
      $item = $queue_item->data;
      if ($item instanceof Item) {
        return $queue_item;
      }
      else {
        // A bad item, so delete it.
        $this->queue->deleteItem($queue_item);
        // Try another.
        return $this->getQueueItem();
      }
    }

    // No items currently available to claim.
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function findLinks(ItemInterface $item) {
    return $this->getEntityHandler()->findLinks($item->entityType(), $item->entityId());
  }

  /**
   * @inheritDoc
   */
  public function correctLinks($links) {
    return $this->getCorrector()->multipleLinks($links);
  }

  /**
   * @inheritDoc
   */
  public function detectedExcessiveRedirects() {
    // TODO: Implement detectedExcessiveRedirects() method.
  }

}
