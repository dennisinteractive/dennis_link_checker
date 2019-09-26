<?php

/**
* @file
* Asset Field
*/
namespace Dennis\Asset\Checker;
use Dennis\Link\Checker\Field;


/**
 * Class AssetField
 * @package Dennis\Asset\Checker
 */
class AssetField extends Field {

  /**
   * Get Assets from field.
   * @return array
   */
  public function getAssets($dom_tag_name) {
    $found = [];

    $assets = $this->getDOM()->getElementsByTagName($dom_tag_name);

    foreach ($assets as $assetElement) {
      $src = $assetElement->getAttribute('src');
      $found[] = new Asset($this->getConfig(), $src, $assetElement);
    }
    return $found;
  }
}