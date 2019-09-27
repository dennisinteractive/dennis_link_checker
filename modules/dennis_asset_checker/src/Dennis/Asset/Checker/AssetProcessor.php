<?php
/**
 * @file
 * Processor
 */

namespace Dennis\Asset\Checker;

use Dennis\Link\Checker\Processor;
use Dennis\Link\Checker\ItemInterface;
use Dennis\Link\Checker\EntityInterface;
use phpDocumentor\Reflection\Types\Boolean;


/**
 * Class Processor
 * @package Dennis\Asset\Checker
 */
class AssetProcessor extends Processor {

  /**
   * @inheritDoc
   */
  public function queueWorker($item) {
    // Not forcing the instance on the function param so that it can fail silently.
    if ($item instanceof ItemInterface) {
      $handler = $this->getEntityHandler();
      $entity = $handler->getEntity($item->entityType(), $item->entityId());
      $field = new AssetField($entity, $item->fieldName());
      $this->correctAssets($item, $field);
    }
  }

  /**
   * correct Assets
   * Heavily borrowed from Processor correctLinks().
   */
  public function correctAssets(ItemInterface $item, AssetField $field) {
    // Potentially the asset types could be moved to a config
    $asset_types = ['embed', 'img'];
    foreach ($asset_types as $asset_type) {
      if ($assets = $field->getAssets($asset_type)) {
        try {
          $assets = $this->getAnalyzer()->multipleAssets($assets);
        } catch (TimeoutException $e) {
          // Log timeout and stop processing this item so that it gets deleted from the queue.
          $this->config->getLogger()->warning($e->getMessage() . ' | '
            . $item->entityType() . '/' . $item->entityId());
          return;
        }

        $do_field_save = FALSE;
        $entity = $field->getEntity();
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
   * @param EntityInterface $entity
   * @param Asset $asset
   *
   * Removes an asset, no point having an embed / image which 404s
   */
  public function removeAsset(EntityInterface $entity, Asset $asset) {
    $asset->remove();
    $this->config->getLogger()->warning('Asset REMOVED | '
      . $entity->entityType() . '/' . $entity->entityId()
      . ' | ' . $asset->originalSrc());

  }


  /**
   * @param EntityInterface $entity
   * @param Asset $asset
   *
   * @return Boolean
   *
   * If possible update our asset with a new URL.
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
