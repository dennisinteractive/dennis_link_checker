<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Component\Utility\Html;
use Drupal\dennis_link_checker\CheckerManagers;

/**
 * Class Field
 *
 * @package Drupal\dennis_link_checker\Dennis\Link\Checker
 */
class Field implements FieldInterface {
  /**
   * @var Entity
   */
  protected $entity;

  /**
   * @var CheckerManagers
   */
  protected $checker_managers;

  /**
   * @var int revision ID
   */
  protected $revision_id;

  /**
   * @var string field name
   */
  protected $field_name;

  /**
   * @var \DOMDocument
   */
  protected $dom;

  /**
   * @var ConfigInterface
   */
  protected $config;


  /**
   * Field constructor.
   *
   * @param EntityInterface $entity
   * @param CheckerManagers $checkerManagers
   * @param $field_name
   */
  public function __construct(EntityInterface $entity,
                              CheckerManagers $checkerManagers,
                              $field_name) {
    $this->entity = $entity;
    $this->checker_managers = $checkerManagers;
    $this->field_name = $field_name;
  }

  /**
   * @inheritDoc
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * @inheritDoc
   */
  protected function getDOM() {
    if (!isset($this->dom)) {
      if ($field_dom = $this->checker_managers->getCheckerQueriesManager()->fieldGetDom(
        $this->getEntity()->entityId(),
        $this->getEntity()->entityType(),
        $this->field_name
      )) {
        if (isset($field_dom['revision_id'])) {
          $this->revision_id = $field_dom['revision_id'];
        }
        if (isset($field_dom['value'])) {
          $this->dom = Html::load($field_dom['value']);
        }
      }
    }
    return $this->dom;
  }

  /**
   * @inheritDoc
   */
  public function getLinks() {
    $found = [];

    $links = $this->getDOM()->getElementsByTagName('a');

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
                $this->checker_managers,
                $this->getConfig(),
                $href,
                $linkElement
              );
            }
          }
          elseif ($parsed['host'] == $this->getConfig()->getSiteHost()) {
            // A full url, but local
            $found[] = new Link(
              $this->checker_managers,
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
          $this->checker_managers,
          $this->getConfig(),
          $href,
          $linkElement
        );
      }
    }



    return $found;
  }

  /**
   * @inheritDoc
   */
  public function save() {
    $updated_text = Html::serialize($this->getDOM());
    return $this->checker_managers->getCheckerQueriesManager()->fieldSave(
      $this->getEntity()->entityId(),
      'node',
      $this->field_name,
      $this->revision_id,
      $updated_text
    );
  }

  /**
   * @inheritDoc
   */
  public function getConfig() {
    return $this->entity->getConfig();
  }
}
