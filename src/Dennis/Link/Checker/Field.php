<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Component\Utility\Html;
use Drupal\dennis_link_checker\CheckerManagers;

/**
 * Class Field.
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Field implements FieldInterface {

  /**
   * Entity.
   *
   * @var Entity
   */
  protected $entity;

  /**
   * Checker managers.
   *
   * @var \Drupal\dennis_link_checker\CheckerManagers
   */
  protected $checkerManagers;

  /**
   * Revision ID.
   *
   * @var int
   */
  protected $revisionId;

  /**
   * Field name.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * Dom element.
   *
   * @var \DOMDocument
   */
  protected $dom;

  /**
   * ConfigInterface.
   *
   * @var ConfigInterface
   */
  protected $config;

  /**
   * Field constructor.
   *
   * @param EntityInterface $entity
   *   Entity.
   * @param \Drupal\dennis_link_checker\CheckerManagers $checkerManagers
   *   Checker Managers.
   * @param string $field_name
   *   Field name.
   */
  public function __construct(EntityInterface $entity,
                              CheckerManagers $checkerManagers,
                              $field_name) {
    $this->entity = $entity;
    $this->checkerManagers = $checkerManagers;
    $this->fieldName = $field_name;
  }

  /**
   * {@inheritDoc}
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * {@inheritDoc}
   */
  protected function getDom() {
    if (!isset($this->dom)) {
      if ($field_dom = $this->checkerManagers->getCheckerQueriesManager()->fieldGetDom(
        $this->getEntity()->entityId(),
        $this->getEntity()->entityType(),
        $this->fieldName
      )) {
        if (isset($field_dom['revision_id'])) {
          $this->revisionId = $field_dom['revision_id'];
        }
        if (isset($field_dom['value'])) {
          $this->dom = Html::load($field_dom['value']);
        }
      }
    }
    return $this->dom;
  }

  /**
   * {@inheritDoc}
   */
  public function getLinks() {
    $found = [];

    $links = $this->getDom()->getElementsByTagName('a');

    /** @var \DOMElement $linkElement */
    foreach ($links as $linkElement) {
      $href = $linkElement->getAttribute('href');
      if ($this->getConfig()->internalOnly()) {
        // Only get local links.
        if ($parsed = parse_url($href)) {
          if (empty($parsed['host'])) {

            if (!empty($parsed['path']) && $parsed['path'][0] == '/') {
              // A valid local link.

              $found[] = new Link(
                $this->checkerManagers,
                $this->getConfig(),
                $href,
                $linkElement
              );
            }
          }
          elseif ($parsed['host'] == $this->getConfig()->getSiteHost()) {
            // A full url, but local.
            $found[] = new Link(
              $this->checkerManagers,
              $this->getConfig(),
              $href,
              $linkElement
            );
          }
        }
      }
      else {
        // All links.
        $found[] = new Link(
          $this->checkerManagers,
          $this->getConfig(),
          $href,
          $linkElement
        );
      }
    }

    return $found;
  }

  /**
   * {@inheritDoc}
   */
  public function save() {
    $updated_text = Html::serialize($this->getDom());
    return $this->checkerManagers->getCheckerQueriesManager()->fieldSave(
      $this->getEntity()->entityId(),
      'node',
      $this->fieldName,
      $this->revisionId,
      $updated_text
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getConfig() {
    return $this->entity->getConfig();
  }

}
