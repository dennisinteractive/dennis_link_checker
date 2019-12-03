<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Component\Utility\Html;
use Drupal\Core\Database\Connection;

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
   * @var Connection
   */
  protected $connection;

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
   * @inheritDoc
   */
  public function __construct(EntityInterface $entity, Connection $connection, $field_name) {
    $this->entity = $entity;
    $this->connection = $connection;
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
      $value_field = $this->field_name . '_value';

      $query = $this->connection->select('field_data_' . $this->field_name, 't');
      $query->addField('t', $value_field);
      $query->addField('t', 'revision_id');
      $query->condition('entity_id', $this->getEntity()->entityId());
      $query->condition('entity_type', $this->getEntity()->entityType());
      $query->condition('delta', 0);
      $result = $query->execute()->fetchObject();

      $this->revision_id = $result->revision_id;

      // Convert all Windows and Mac newlines to a single newline, so filters only
      // need to deal with one possibility.
      // This has been copied from check_markup().
      $value = str_replace(["\r\n", "\r"], "\n", $result->{$value_field});

      $this->dom = Html::load($value);
    }

    return $this->dom;
  }

  /**
   * @inheritDoc
   */
  public function getLinks() {
    $found = [];

    $links = $this->getDOM()->getElementsByTagName('a');
    foreach ($links as $linkElement) {
      $href = $linkElement->getAttribute('href');
      if ($this->getConfig()->internalOnly()) {
        // Only get local links.
        if ($parsed = parse_url($href)) {
          if (empty($parsed['host'])) {
            if (!empty($parsed['path']) && $parsed['path'][0] == '/') {
              // A valid local link.
              $found[] = new Link($this->connection, $this->getConfig(), $href, $linkElement);
            }
          }
          elseif ($parsed['host'] == $this->getConfig()->getSiteHost()) {
            // A full url, but local
            $found[] = new Link($this->connection, $this->getConfig(), $href, $linkElement);
          }
        }
      }
      else {
        // All links.
        $found[] = new Link($this->connection, $this->getConfig(), $href, $linkElement);
      }
    }

    return $found;
  }

  /**
   * @inheritDoc
   */
  public function save() {
    $updated = 0;

    $updated_text = Html::serialize($this->getDOM());

    foreach (['data', 'revision'] as $table_type) {

      $updated += $this->connection->update('field_' . $table_type . '_' . $this->field_name)
        ->fields([
          $this->field_name . '_value' => $updated_text
        ])
        ->condition('entity_id', $this->getEntity()->entityId())
        ->condition('entity_type', $this->getEntity()->entityType())
        ->condition('revision_id', $this->revision_id)
        // Hardcoded delta so only the first value of a multivalue field is used.
        ->condition('delta', 0)
        ->execute();
    }

    return $updated;
  }

  /**
   * @inheritDoc
   */
  public function getConfig() {
    return $this->entity->getConfig();
  }
}
