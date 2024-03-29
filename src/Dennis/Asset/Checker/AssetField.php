<?php

namespace Drupal\dennis_link_checker\Dennis\Asset\Checker;

use Drupal\dennis_link_checker\Dennis\Link\Checker\Field;

/**
 * Class AssetField.
 *
 * @package Dennis\Asset\Checker
 */
class AssetField extends Field {

  /**
   * Get Assets from field.
   *
   * @return array
   *   Returns array of found assets.
   */
  public function getAssets($dom_tag_name) {
    $found = [];

    $assets = $this->getDOM()->getElementsByTagName($dom_tag_name);

    /** @var \DOMElement $assetElement */
    foreach ($assets as $assetElement) {

      $src = $assetElement->getAttribute('src');
      $found[] = new Asset(
        $this->checkerManagers,
        $this->getConfig(),
        $src,
        $assetElement
      );
    }
    return $found;
  }

}
