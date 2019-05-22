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

  protected $entityHandler;

  protected $analyzer;

  protected $timeLimit = 1800;

  protected $localisation;

  // A count of the number of links which were checked.
  protected $numberChecked = 0;

  // A list of the URLs which have been updated.
  protected $linksDeleted = [];

  // A list of the URLs which have been updated.
  protected $linksUpdated = [];

  // A list of the URLs which have weren't updated.
  protected $linksNotUpdated = [];

  // An array of the HTTP errors encountered in the run, including 404s and 30xs.
  protected $errorsEncountered = [];

  // An array of links which led to a 404 error.
  protected $notFounds = [];

  // An array of links 404'd which have been removed.
  protected $notFoundsFixed = [];

  // An array listing the redirect loops which were found, if any.
  protected $redirectLoopsFound = [];

  // An array listing the redirect loops which were removed, if any.
  protected $redirectLoopsRemoved = [];

  /**
   * @return mixed
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * @return mixed
   */
  public function getLocalisation() {
    return $this->localisation;
  }

  /**
   * @return int
   */
  public function getNumberChecked() {
    return $this->numberChecked;
  }

  /**
   * @return array
   */
  public function getLinksDeleted() {
    return $this->linksDeleted;
  }

  /**
   * @return array
   */
  public function getLinksUpdated() {
    return $this->linksUpdated;
  }

  /**
   * @return array
   */
  public function getLinksNotUpdated() {
    return $this->linksNotUpdated;
  }

  /**
   * @return array
   */
  public function getErrorsEncountered() {
    return $this->errorsEncountered;
  }

  /**
   * @return array
   */
  public function getNotFounds() {
    return $this->notFounds;
  }

  /**
   * @return array
   */
  public function getNotFoundsFixed() {
    return $this->notFoundsFixed;
  }

  /**
   * @return array
   */
  public function getRedirectLoopsFound() {
    return $this->redirectLoopsFound;
  }

  /**
   * @return array
   */
  public function getRedirectLoopsRemoved() {
    return $this->redirectLoopsRemoved;
  }

  /**
   * ProcessorInterface constructor.
   *
   * @param \Dennis\Link\Checker\ConfigInterface $config
   * @param \Dennis\Link\Checker\QueueInterface $queue
   * @param \Dennis\Link\Checker\EntityHandlerInterface $entity_handler
   * @param \Dennis\Link\Checker\AnalyzerInterface $analyzer
   */
  public function __construct(
    ConfigInterface $config,
    DrupalReliableQueueInterface $queue,
    EntityHandlerInterface $entity_handler,
    AnalyzerInterface $analyzer) {
      $this->setConfig($config);
      $this->setQueue($queue);
      $this->setEntityHandler($entity_handler);
      $this->setAnalyzer($analyzer);
  }

  /**
   * @inheritDoc
   */
  public function run() {
    // Prevent processing of links when site is in maintenance mode.
    if ($this->inMaintenanceMode()) {
      $this->getConfig()->getLogger()->info('Links cannot be processed when the site is in maintenance mode.');
      return FALSE;
    }

    $end = time() + $this->getTimeLimit();

    // Remove any old items from the queue.
    $this->getQueue()->removeAll();

    // Make sure there is something to do.
    $this->ensureEnqueued();

    $ok_to_continue = TRUE;

    while ($ok_to_continue && (time() < $end)) {
      try {
        $ok_to_continue = $this->doNextItem();
      } catch (RequestTimeoutException $e) {
        // Don't try to process any more items for this run.
        $this->getConfig()->getLogger()->warning($e->getMessage());
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Whether the site is in maintenance mode.
   */
  public function inMaintenanceMode() {
    return variable_get('maintenance_mode', 0);
  }

  /**
   * @inheritDoc
   */
  public function getQueue() {
    return $this->queue;
  }

  /**
   * Removes old items.
   */
  public function prune() {
    $this->getQueue()->prune();
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
  public function setConfig(ConfigInterface $config) {
    $this->config = $config;
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
    $this->entityHandler = $entity_handler;
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
    return $this->entityHandler;
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
      $field_names = $this->getConfig()->getFieldNames();
      foreach ($field_names as $field_name) {
        $this->enqueue($field_name);
      }
    }
  }

  /**
   * @inheritDoc
   */
  public function enqueue($field_name) {
    // Find entities that have a text area field with a link.
    $query = db_select('field_data_' . $field_name, 'b');

    // The entity may not be a node.
    $query->leftJoin('node', 'n', 'n.nid = b.entity_id');
    $query->addField('b', 'entity_id');
    $query->addField('b', 'entity_type');
    $query->addField('n', 'vid', 'entity_vid');

    // Nodes only if they are published.
    $or = db_or()->condition('n.status', 1)->isNull('n.status');
    $query->condition($or);

    // Crudely find things that could be links.
    // Accurate link finding happens when the queue is processed.
    $query->condition($field_name . '_value', '%' . db_like('<a') . '%', 'LIKE');

    // Optionally limit the result set.
    $nids = $this->getConfig()->getNodeList();

    if (!empty($nids)) {
      $query->condition('n.nid', $nids, 'IN');
    }

    // Only process nodes which haven't been checked, or which haven't been
    // checked in the last N days.
    $query->leftJoin('dennis_link_checker_checked_nodes', 'cn', 'n.vid = cn.vid AND cn.field_name = :field_name', [':field_name' => $field_name]);

    $or = db_or()->isNull('cn.last_checked')
      ->condition('cn.last_checked', REQUEST_TIME - (variable_get(DENNIS_LINK_CHECKER_VARIABLE_CHECK_FREQUENCY, DENNIS_LINK_CHECKER_CHECK_FREQUENCY_DEFAULT) * 24 * 60 * 60), '<');
    $query->condition($or);

    $query->orderBy('b.entity_id', 'DESC');

    $result = $query->execute();

    foreach ($result as $record) {
      $this->addItem(new Item($record->entity_type, $record->entity_id, $record->entity_vid, $field_name));
    }
  }

  /**
   * @inheritDoc
   */
  public function addItem(ItemInterface $item) {
    // Add the item to the queue
    return $this->getQueue()->createItem($item);
  }

  /**
   * @inheritDoc
   */
  public function numberOfItems() {
    return $this->getQueue()->numberOfItems();
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
    $this->getQueue()->deleteItem($queue_item);
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
    if ($queue_item = $this->getQueue()->claimItem()) {
      $item = $queue_item->data;
      if ($item instanceof Item) {
        return $queue_item;
      }
      else {
        // A bad item, so delete it.
        $this->getQueue()->deleteItem($queue_item);

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
        $this->getConfig()->getLogger()->warning($e->getMessage() . ' | '
          . $item->entityType() . '/' . $item->entityId());
        return;
      }

      $do_field_save = FALSE;
      $entity = $field->getEntity();

      foreach ($links as $link) {
        $this->numberChecked++;
        $this->getAnalyzer()->updateStatistics('number_links_checked');

        if ($err = $link->getError()) {
          if ($link->getNumberOfRedirects() > $this->getConfig()->getMaxRedirects()) {
            $msg = 'Excessive Redirects on: '
              . $entity->entityType() . '/' . $entity->entityId()
              . ' to ' . $link->originalHref();
            $this->getConfig()->getLogger()->warning($msg);
            $this->getAnalyzer()->updateStatistics('redirect_loops_found', ['node' => $item->entityId(), 'link' => $link->getData()]);
          }
          else {
            $this->getConfig()->getLogger()->error('Error when visiting: ' . $link->originalHref(), $err);
            $this->getAnalyzer()->updateStatistics('errors_encountered', ['node' => $item->entityId(), 'link' => $link->getData(), 'error' => $err]);
          }
        }
        else {
          $this->getConfig()->getLogger()->debug(
            $entity->entityType() . '/' . $entity->entityId()
            . ' : ' . $link->getNumberOfRedirects()
            . ' : ' . $link->originalHref()
          );

          // SEO want a report of 404's.
          if ($link->getHttpCode() == 404) {
            $suggested = $link->suggestLink($link->originalHref());
            $suggested = empty($suggested) ? 'No suggestion' : 'Suggest : ' . $suggested;

            $this->getAnalyzer()->updateStatistics('404s_found', ['node' => $item->entityId(), 'link' => $link->getData(), 'suggestedLink' => $suggested]);

            $this->getConfig()->getLogger()->warning('Page Not Found | '
              . $entity->entityType() . '/' . $entity->entityId()
              . ' | '. $link->originalHref()
              . ' => ' . $suggested);
          }

          // Do the correction if needed.
          if ($link->corrected() && $this->updateLink($entity, $link)) {
            $do_field_save = TRUE;
            $this->getAnalyzer()->updateStatistics('links_updated', ['node' => $item->entityId(), 'link' => $link->getData()]);
          }
          else {
            $this->getAnalyzer()->updateStatistics('links_not_updated', ['node' => $item->entityId(), 'link' => $link->getData()]);
          }
        }
      }

      if ($do_field_save) {
        $field->save();
      }
    }

    // Make a record of the field being checked.
    $item->recordItemProcessed();

    // Make a record of the node being checked.
    $number_nodes_checked_array = $this->getAnalyzer()->getStatistics('number_nodes_checked');
    $number_nodes_checked_array[$item->entityId()] = TRUE;
    $this->getAnalyzer()->setStatistics('number_nodes_checked', $number_nodes_checked_array);
  }

  /**
   * Updates a link.
   */
  public function updateLink(EntityInterface $entity, LinkInterface $link) {
    // Before doing the replacement, check if the link originally pointed to a node, and
    // now points to a term, and if so then remove the link altogether. See case 27710.
    if ($this->getConfig()->removeTermLinks() && $link->redirectsToTerm()) {
      // Strip link and keep the text part
      $link->strip();
      $this->getConfig()->getLogger()->warning('LINK REMOVED | '
        . $entity->entityType() . '/' . $entity->entityId()
        . ' | ' . $link->originalHref() . " => " . $link->correctedHref());
      $this->getAnalyzer()->updateStatistics('links_deleted', ['node' => $entity->entityId(), 'link' => $link->getData()]);
    }
    else {
      if ($link->replace()) {
        $this->getConfig()->getLogger()->info('Link corrected | '
          . $entity->entityType() . '/' . $entity->entityId()
          . ' | ' . $link->originalHref() . " => " . $link->correctedHref());
        $this->getAnalyzer()->updateStatistics('links_updated', ['node' => $entity->entityId(), 'link' => $link->getData()]);
      }
      else {
        $this->getConfig()->getLogger()->info('Link NOT corrected | '
          . $entity->entityType() . '/' . $entity->entityId()
          . ' | ' . $link->originalHref() . " => " . $link->correctedHref());
        $this->getAnalyzer()->updateStatistics('links_not_updated', ['node' => $entity->entityId(), 'link' => $link->getData()]);
        return FALSE;
      }
    }
    return TRUE;
  }
}
