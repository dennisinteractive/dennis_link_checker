<?php

namespace Drupal\dennis_link_checker\Dennis\Asset\Checker;

use Drupal\dennis_link_checker\Dennis\Link\Checker\Processor;
use Drupal\dennis_link_checker\Dennis\Link\Checker\ItemInterface;
use Drupal\dennis_link_checker\Dennis\Link\Checker\EntityInterface;
use Drupal\dennis_link_checker\Dennis\Link\Checker\TimeoutException;

/**
 * Class Processor.
 *
 * @package Dennis\Asset\Checker
 */
class AssetProcessor extends Processor {

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\dennis_link_checker\Dennis\Link\Checker\RequestTimeoutException
   */
  public function queueWorker($item) {
    // Not forcing the instance on the function param so that it can fail silently.
    if ($item instanceof ItemInterface) {
      $handler = $this->getEntityHandler();
      $entity = $handler->getEntity($item->entityType(), $item->entityId());
      $field = new AssetField(
        $entity,
        $this->checkerManagers,
        $item->fieldName()
      );
      $this->correctAssets($item, $field);
    }
  }

  /**
   * Correct Assets.
   *
   * Heavily borrowed from Processor correctLinks().
   *
   * @param \Drupal\dennis_link_checker\Dennis\Link\Checker\ItemInterface $item
   *   Item interface.
   * @param AssetField $field
   *   Asset field.
   *
   * @throws \Drupal\dennis_link_checker\Dennis\Link\Checker\RequestTimeoutException
   */
  public function correctAssets(ItemInterface $item, AssetField $field) {
    // Potentially the asset types could be moved to a config.
    $do_field_save = FALSE;
    $asset_types = ['embed', 'img'];
    foreach ($asset_types as $asset_type) {
      if ($assets = $field->getAssets($asset_type)) {
        try {
          /** @var \Drupal\dennis_link_checker\Dennis\Asset\Checker\AssetAnalyser $analyzer */
          $analyzer = $this->getAnalyzer();
          $assets = $analyzer->multipleAssets($assets);
        }
        catch (TimeoutException $e) {
          // Log timeout and stop processing this item so that it gets deleted from the queue.
          $this->config->getLogger()->warning($e->getMessage() . ' | '
            . $item->entityType() . '/' . $item->entityId());
          return;
        }

        $entity = $field->getEntity();
        /** @var \Drupal\dennis_link_checker\Dennis\Asset\Checker\Asset $asset */
        foreach ($assets as $asset) {
          // As we log 404 links, I figured we might as well keep the logging for broken assets.
          if ($asset->getHttpCode() == 404 || $asset->getHttpCode() == 403) {
            $suggested = 'No Suggestion';
            $this->notFounds = $asset;
            $this->config->getLogger()->warning('Asset Not Found | '
              . $entity->entityType() . '/' . $entity->entityId()
              . ' | ' . $asset->originalSrc()
              . ' => ' . $suggested);

            $this->removeAsset($entity, $asset);
            $do_field_save = TRUE;
          }

          // Do the correction if needed.
          if ($asset->corrected() && $this->updateAsset($entity, $asset)) {
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
   * Removes an asset, no point having an embed / image which 404s.
   *
   * @param \Drupal\dennis_link_checker\Dennis\Link\Checker\EntityInterface $entity
   *   Entity interface.
   * @param Asset $asset
   *   Asset object.
   */
  public function removeAsset(EntityInterface $entity, Asset $asset) {
    $asset->remove();
    $this->config->getLogger()->warning('Asset REMOVED | '
      . $entity->entityType() . '/' . $entity->entityId()
      . ' | ' . $asset->originalSrc());

  }

  /**
   * If possible update our asset with a new URL.
   *
   * @param \Drupal\dennis_link_checker\Dennis\Link\Checker\EntityInterface $entity
   *   Entity interface.
   * @param Asset $asset
   *   Asset object.
   *
   * @return bool
   *   If asset corrected returns TRUE, else FALSE.
   */
  public function updateAsset(EntityInterface $entity, Asset $asset) {
    if ($asset->replace()) {
      $this->config->getLogger()->info('Asset corrected | '
        . $entity->entityType() . '/' . $entity->entityId()
        . ' | ' . $asset->originalSrc() . " => " . $asset->getFoundUrl());
      return TRUE;
    }
    else {
      $this->config->getLogger()->info('Asset NOT corrected | '
        . $entity->entityType() . '/' . $entity->entityId()
        . ' | ' . $asset->originalSrc());
      return FALSE;
    }
  }

}
