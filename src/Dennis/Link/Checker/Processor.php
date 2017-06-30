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

  protected $notFounds = [];


  /**
   * ProcessorInterface constructor.
   *
   * @param ConfigInterface $config
   * @param DrupalReliableQueueInterface $queue
   * @param EntityHandlerInterface $entity_handler
   * @param AnalyzerInterface $analyzer
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
    // Prevent processing of links when site is in maintenance mode.
    if (variable_get('maintenance_mode', 0)) {
      $this->config->getLogger()->info('Links cannot be processed when the site is in maintenance mode.');
      return;
    }

    $end = time() + $this->timeLimit;

    // Remove any old items from the queue.
    $this->queue->prune();

    // Make sure there is something to do.
    $this->ensureEnqueued();

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
      $field_names = $this->config->getFieldNames();
      foreach ($field_names as $field_name) {
        $this->enqueue($field_name);
      }
    }
  }

  /**
   * @inheritDoc
   */
  public function enqueue($field_name) {
    // entities that have a text area field with a link.
    // Just the body text field for now.
    $query = db_select('field_data_' . $field_name, 'b');
    // The entity may not be a node.
    $query->leftJoin('node', 'n', 'n.nid = b.entity_id');
    $query->addField('b', 'entity_id');
    $query->addField('b', 'entity_type');
    // Nodes only if they are published.
    $or = db_or()->condition('n.status', 1)->isNull('n.status');
    $query->condition($or);

    // Crudely find things that could be links.
    // Accurate link finding happens when the queue is processed.
    $query->condition($field_name . '_value', '%' . db_like('<a') . '%', 'LIKE');

    // Optionally limit the result set
    $nids = $this->config->getNodeList();
    if (!empty($nids)) {
      $query->condition('n.nid', $nids, 'IN');
    }

    $query->orderBy('b.entity_id', 'DESC');

    $result = $query->execute();

    foreach ($result as $record) {
      $this->addItem(new Item($record->entity_type, $record->entity_id, $field_name));
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
    if ($item instanceof ItemInterface) {
      $field = $this->getEntityHandler()
        ->getEntity($item->entityType(), $item->entityId())
        ->getField($item->fieldName());
      $this->correctLinks($item, $field);
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
  public function correctLinks(ItemInterface $item, FieldInterface $field) {
    if ($links = $field->getLinks()) {
      // Check all the links.
      try {
        $links = $this->getAnalyzer()->multipleLinks($links);
      }
      catch (TimeoutException $e) {
        // Log timeout and stop processing this item so that it gets deleted from the queue.
        $this->config->getLogger()->warning($e->getMessage() . ' | '
          . $item->entityType() . '/' . $item->entityId());
        return;
      }

      $do_field_save = FALSE;
      $entity = $field->getEntity();

      foreach ($links as $link) {
        if ($err = $link->getError()) {
          if ($link->getNumberOfRedirects() > $this->config->getMaxRedirects()) {
            $msg = 'Excessive Redirects on: '
              . $entity->entityType() . '/' . $entity->entityId()
              . ' to ' . $link->originalHref();
            $this->config->getLogger()->warning($msg);
          }
          else {
            $this->config->getLogger()->error('Error when visiting: ' . $link->originalHref(), $err);
          }
        }
        else {

          $this->config->getLogger()->debug(
            $entity->entityType() . '/' . $entity->entityId()
            . ' : ' . $link->getNumberOfRedirects()
            . ' : ' . $link->originalHref()
          );

          // SEO want a report of 404's.
          if ($link->getHttpCode() == 404) {
            $suggested = $link->suggestLink($link->originalHref());
            $suggested = empty($suggested) ? 'No suggestion' : 'Suggest : ' . $suggested;
            $this->notFounds = $link;
            $this->config->getLogger()->warning('Page Not Found | '
              . $entity->entityType() . '/' . $entity->entityId()
              . ' | '. $link->originalHref()
              . ' => ' . $suggested);
          }

          // Do the correction if needed.
          if ($link->corrected() && $this->updateLink($entity, $link)) {
            $do_field_save = TRUE;
          }

        }
      }
      if ($do_field_save) {
        $field->save();
      }
    }
  }

  /**
   * Updates a link.
   */
  public function updateLink(EntityInterface $entity, LinkInterface $link) {
    // Before doing the replacement, check if the link originally pointed to a node, and
    // now points to a term, and if so then remove the link altogether. See case 27710.
    if ($this->config->removeTermLinks() && $link->redirectsToTerm()) {
      // Strip link and keep the text part
      $link->strip();
      $this->config->getLogger()->warning('LINK REMOVED | '
        . $entity->entityType() . '/' . $entity->entityId()
        . ' | ' . $link->originalHref() . " => " . $link->correctedHref());
    }
    else {
      if ($link->replace()) {
        $this->config->getLogger()->info('Link corrected | '
          . $entity->entityType() . '/' . $entity->entityId()
          . ' | ' . $link->originalHref() . " => " . $link->correctedHref());
      }
      else {
        $this->config->getLogger()->info('Link NOT corrected | '
          . $entity->entityType() . '/' . $entity->entityId()
          . ' | ' . $link->originalHref() . " => " . $link->correctedHref());
        return FALSE;
      }
    }
    return TRUE;
  }

}
