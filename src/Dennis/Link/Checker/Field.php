<?php

namespace Drupal\dennis_link_checker\Dennis\Link\Checker;

use Drupal\Component\Utility\Html;
use Drupal\Core\Database\Connection;
use Drupal\dennis_link_checker\Dennis\CheckerManagers;

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
   * @param Connection $connection
   * @param CheckerManagers $checkerManagers
   * @param $field_name
   */
  public function __construct(EntityInterface $entity,
                              Connection $connection,
                              CheckerManagers $checkerManagers,
                              $field_name) {
    $this->entity = $entity;
    $this->connection = $connection;
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
      $value_field = $this->field_name . '_value';
      if ($this->connection->schema()->tableExists('node__' . $this->field_name)) {
        $query = $this->connection->select('node__' . $this->field_name, 't');
        $query->addField('t', $value_field);
        $query->addField('t', 'revision_id');
        $query->condition('entity_id', $this->getEntity()->entityId());
        $query->condition('bundle', $this->getEntity()->entityType());
        $query->condition('delta', 0);
        $result = $query->execute()->fetchObject();
        $this->revision_id = $result->revision_id;
      }
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
                $this->connection,
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
              $this->connection,
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
          $this->connection,
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
    $updated = 0;

    $updated_text = Html::serialize($this->getDOM());

    foreach (['_', 'revision__'] as $table_type) {
      $table = 'node_' . $table_type . $this->field_name;
      if ($this->connection->schema()->tableExists($table)) {
        $updated += $this->connection->update($table)
          ->fields([
            $this->field_name . '_value' => $updated_text
          ])
          ->condition('entity_id', $this->getEntity()->entityId())
          ->condition('bundle', $this->getEntity()->entityType())
          ->condition('revision_id', $this->revision_id)
          // Hardcoded delta so only the first value of a multivalue field is used.
          ->condition('delta', 0)
          ->execute();
      }
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
