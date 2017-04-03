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

  protected $timeLimit;

  /**
   * @inheritDoc
   */
  public function __construct(DrupalReliableQueueInterface $queue, $time_limit = 1800) {
    $this->queue = $queue;
    $this->timeLimit = $time_limit;
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
  public function enqueue() {
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
    $texts = $this->getTexts($item);
    foreach ($texts as $text) {
      // Find links in text area fields
      $links = $this->findLinks($text);

      // Check each one.
      foreach ($links as $link) {
        $checked = $this->checkLink($link);
        // Re-save the field(s) that have changed links.
        //@todo re save links.
        if ($checked != $link) {

        }
      }
    }

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
  public function getTexts(ItemInterface $item) {
    // TODO: Implement getTexts() method.

    return ['body' => 'foo <a href="/bar">bar</a>'];
  }


  /**
   * @inheritDoc
   */
  public function findLinks($text) {
    // TODO: Implement findLinks() method.

    return [];
  }

  /**
   * @inheritDoc
   */
  public function checkLink($url) {
    // TODO: Implement checkLink() method.

    return $url;
  }


  /**
   * @inheritDoc
   */
  public function detectedExcessiveRedirects() {
    // TODO: Implement detectedExcessiveRedirects() method.
  }

}
