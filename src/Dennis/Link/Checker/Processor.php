<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Core\State\State;
use Drupal\Core\Queue\ReliableQueueInterface;
use Drupal\dennis_link_checker\CheckerManagers;

/**
 * Class Processor.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Processor implements ProcessorInterface {

  /**
   * Config interface.
   *
   * @var ConfigInterface
   */
  protected $config;

  /**
   * Reliable queue interface.
   *
   * @var \Drupal\Core\Queue\ReliableQueueInterface
   */
  protected $queue;

  /**
   * Entity handler interface.
   *
   * @var EntityHandlerInterface
   */
  protected $entityHandeler;

  /**
   * Analyzer interface.
   *
   * @var AnalyzerInterface
   */
  protected $analyzer;

  /**
   * Checker managers.
   *
   * @var \Drupal\dennis_link_checker\CheckerManagers
   */
  protected $checkerManagers;

  /**
   * State.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * Time limit.
   *
   * @var int
   */
  protected $timeLimit = 2700;

  /**
   * Localisation string.
   *
   * @var string
   */
  protected $localisation;

  /**
   * Not found array.
   *
   * @var array
   */
  protected $notFounds = [];

  /**
   * Processor constructor.
   *
   * @param ConfigInterface $config
   *   Config interface.
   * @param \Drupal\Core\Queue\ReliableQueueInterface $queue
   *   Reliable Queue Interface.
   * @param EntityHandlerInterface $entity_handler
   *   Entity handler interface.
   * @param AnalyzerInterface $analyzer
   *   Analyzer interface.
   * @param \Drupal\dennis_link_checker\CheckerManagers $checkerManagers
   *   Checker Managers.
   * @param \Drupal\Core\State\State $state
   *   State.
   */
  public function __construct(ConfigInterface $config,
                              ReliableQueueInterface $queue,
                              EntityHandlerInterface $entity_handler,
                              AnalyzerInterface $analyzer,
                              CheckerManagers $checkerManagers,
                              State $state) {
    $this->setConfig($config);
    $this->setQueue($queue);
    $this->setEntityHandler($entity_handler);
    $this->setAnalyzer($analyzer);
    $this->checkerManagers = $checkerManagers;
    $this->state = $state;
  }

  /**
   * {@inheritDoc}
   */
  public function run() {
    // Prevent processing of links when site is in maintenance mode.
    if ($this->inMaintenanceMode()) {
      $this->config->getLogger()->info('Links cannot be processed when the site is in maintenance mode.');
      return FALSE;
    }

    // Clear the queue if nids are present as parameters.
    if (!empty($this->config->getNodeList())) {
      $this->getQueue()->deleteQueue();
    }
    $end = time() + $this->timeLimit;

    // Remove any old items from the queue.
    $this->prune();

    // Make sure there is something to do.
    $this->ensureEnqueued();

    $more = TRUE;

    while ($more && time() < $end) {
      try {
        $more = $this->doNextItem();
      }
      catch (RequestTimeoutException $e) {
        // Log the timeout, but keep going.
        $this->config->getLogger()->warning($e->getMessage());
      }
    }

    return TRUE;
  }

  /**
   * Whether the site is in maintenance mode.
   */
  public function inMaintenanceMode() {
    return $this->state->get('system.maintenance_mode', 0);
  }

  /**
   * {@inheritDoc}
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
   * {@inheritDoc}
   */
  public function setTimeLimit($time_limit) {
    $this->timeLimit = $time_limit;
  }

  /**
   * {@inheritDoc}
   */
  public function getTimeLimit() {
    return $this->timeLimit;
  }

  /**
   * {@inheritDoc}
   */
  public function setConfig(ConfigInterface $config) {
    $this->config = $config;
  }

  /**
   * {@inheritDoc}
   */
  public function setQueue(ReliableQueueInterface $queue) {
    $this->queue = $queue;
  }

  /**
   * {@inheritDoc}
   */
  public function setEntityHandler(EntityHandlerInterface $entity_handler) {
    $this->entityHandeler = $entity_handler;
  }

  /**
   * {@inheritDoc}
   */
  public function setAnalyzer(AnalyzerInterface $analyzer) {
    $this->analyzer = $analyzer;
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityHandler() {
    return $this->entityHandeler;
  }

  /**
   * {@inheritDoc}
   */
  public function getAnalyzer() {
    return $this->analyzer;
  }

  /**
   * {@inheritDoc}
   */
  public function setLocalisation($localisation) {
    $this->localisation = $localisation;
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function localisation() {
    return $this->localisation;
  }

  /**
   * {@inheritDoc}
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
   * {@inheritDoc}
   */
  public function enqueue($field_name) {
    if ($result = $this->checkerManagers->getCheckerQueriesManager()->enqueue(
      $field_name,
      $this->config->getNodeList()
    )) {
      foreach ($result as $record) {
        $this->addItem(new Item($record->bundle, $record->entity_id, $field_name));
      }
    }
  }

  /**
   * {@inheritDoc}
   */
  public function addItem(ItemInterface $item) {
    // Add the item to the queue.
    return $this->queue->createItem($item);
  }

  /**
   * {@inheritDoc}
   */
  public function numberOfItems() {
    return $this->queue->numberOfItems();
  }

  /**
   * {@inheritDoc}
   */
  public function doNextItem() {
    if (!$queue_item = $this->getQueueItem()) {
      throw new RequestTimeoutException();
    }

    $this->queueWorker($queue_item->data);

    // Remove it from the queue.
    $this->queue->deleteItem($queue_item);
    return TRUE;
  }

  /**
   * {@inheritDoc}
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
   * {@inheritDoc}
   */
  public function getQueueItem() {
    // Claim item from queue.
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
   * {@inheritDoc}
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
      /** @var \Drupal\dennis_link_checker\Dennis\Link\Checker\Link $link */
      foreach ($links as $link) {
        // If url is protocol neutral, force it to use http.
        if (substr($link->originalHref(), 0, 2) === "//") {
          $url = ltrim($link->originalHref(), '//');
          $link->setOriginalHref('http://' . $url);
        }

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
              . ' | ' . $link->originalHref()
              . ' => ' . $suggested);
          }

          // Remove mce_href parameter from links.
          if ($link->removeMceHref()) {
            $this->config->getLogger()->info('mce_href removed | '
              . $entity->entityType() . '/' . $entity->entityId()
              . ' | ' . $link->originalHref() . " => " . $link->correctedHref());
            $do_field_save = TRUE;
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
   *
   * @param EntityInterface $entity
   *   Entity interface.
   * @param LinkInterface $link
   *   Link interface.
   *
   * @return bool
   *   Returns TRUE.
   */
  public function updateLink(EntityInterface $entity, LinkInterface $link) {
    // Before doing the replacement, check if the link originally pointed to a node, and
    // now points to a term, and if so then remove the link altogether. See case 27710.
    if (($this->config->removeTermLinks() && $link->redirectsToTerm())
       || ($this->config->removeFrontLinks() && $link->redirectsToFront()
    )) {
      // Strip link and keep the text part.
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
