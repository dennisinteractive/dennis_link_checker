<?php
/**
 * @file
 * Processor
 */
namespace Dennis\Link\Checker;
use DrupalReliableQueueInterface;

/**
 * Class Processor
 * @package Dennis\Link\Checker
 */
class Processor implements ProcessorInterface {

  protected $config;

  protected $queue;

  protected $entityHandeler;

  protected $analyzer;

  protected $timeLimit = 1800;

  protected $localisation;

  protected $excessiveRedirects = [];

  protected $notFounds = [];

  /**
   * @inheritDoc
   */
  public function __construct(
    ConfigInterface $config,
    DrupalReliableQueueInterface $queue,
    EntityHandlerInterface $entity_handler,
    AnalyzerInterface $analyzer) {
    $this->config = $config;
    $this->setQueue($queue);
    $this->setEntityHandler($entity_handler);
    $this->setAnalyzer($analyzer);
  }

  /**
   * @inheritDoc
   */
  public function run() {
    $end = time() + $this->timeLimit;

    // Make sure there is something to do.
    $this->ensureEnqueued();

    $more = TRUE;
    while ($more && time() < $end) {
      $more = $this->doNextItem();
    }

    // Log & output excessive redirects.
    $this->outputReport();
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
  public function setAnalyzer(AnalyzerInterface $analyzer) {
    $this->analyzer = $analyzer;
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
  public function getAnalyzer() {
    return $this->analyzer;
  }

  /**
   * @inheritDoc
   */
  public function setLocalisation($localisation) {
    $this->localisation = $localisation;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function localisation() {
    return $this->localisation;
  }

  /**
   * @inheritDoc
   */
  public function ensureEnqueued() {
    // Check for anything in the queue to process.
    if ($this->numberOfItems() == 0) {
      $this->enqueue();
    }
  }

  /**
   * @inheritDoc
   */
  public function enqueue() {
    // entities that have a text area field with a link.


    // Just the body text field for now.
    $query = db_select('field_data_body', 'b');
    // The entity may not be a node.
    $query->leftJoin('node', 'n', 'n.nid = b.entity_id');
    $query->addField('b', 'entity_id');
    $query->addField('b', 'entity_type');
    // Nodes only if they are published.
    $or = db_or()->condition('n.status', 1)->isNull('n.status');
    $query->condition($or);

    // Crudely find things that could be links.
    // Accurate link finding happens when the queue is processed.
    $query->condition('body_value', '%' . db_like('<a') . '%', 'LIKE');

    $query->orderBy('b.entity_id', 'DESC');

    $result = $query->execute();
    foreach ($result as $record) {
      $this->addItem(new Item($record->entity_type, $record->entity_id));
    }

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

    $this->queueWorker($queue_item->data);

    // Remove it from the queue.
    $this->queue->deleteItem($queue_item);
    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function queueWorker($item) {
    // Not forcing the instance on the function param so that it can fail silently.
    if ($item instanceof Item) {
      $links = $this->findLinks($item);
      $this->correctLinks($item, $links);
    }
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
  public function correctLinks(ItemInterface $item, $links) {
    if (count($links) > 0) {
      // Check all the links.
      $links = $this->getAnalyzer()->multipleLinks($links);
      foreach ($links as $link) {
        if ($err = $link->getError()) {
          if ($link->hasTooManyRedirects()) {
            $this->excessiveRedirects[] = $link;
          }
          $this->config->getLogger()->error($link->originalHref(), $err);
        }
        else {

          $this->config->getLogger()->debug(
            $link->entityType() . '/' . $link->entityId()
            . ' : ' . $link->getNumberOfRedirects()
            . ' : ' . $link->originalHref()
          );

          // SEO want a report of 404's.
          if ($link->getHttpCode() == 404) {
            $this->notFounds = $link;
            $this->config->getLogger()->warning('Page Not Found: ' . $link->originalHref());
          }

          // Do the correction if needed.
          if ($link->corrected()) {
            $this->getEntityHandler()->updateLink($link);
          }

        }
      }

    }
  }

  /**
   * @inheritDoc
   */
  public function excessiveRedirects() {
    $msgs = [];
    foreach ($this->excessiveRedirects as $link) {
      $msg = 'Excessive redirects on: ' . $link->entityType() . '/' . $link->entityId();
      $msgs[] = $msg;
      $this->config->getLogger()->warning($msg);
    }

    return $msgs;
  }

  /**
   * @inheritDoc
   */
  public function outputReport() {
    $msgs = $this->excessiveRedirects();
    foreach ($msgs as $msg) {
      watchdog('dennis_link_checker', $msg);
    }

    foreach ($this->notFounds as $link) {
      watchdog('dennis_link_checker', 'Page Not Found: ' . $link->originalHref());
    }
  }

}
