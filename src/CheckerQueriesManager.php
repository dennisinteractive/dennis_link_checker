<?php

namespace Drupal\dennis_link_checker;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;

/**
 * Class CheckerQueriesManager
 *
 * @package Drupal\dennis_link_checker
 */
class CheckerQueriesManager implements CheckerQueriesManagerInterface {

  /***
   * @var Connection
   */
  protected $connection;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CheckerQueriesManager constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(Connection $connection, EntityTypeManagerInterface $entityTypeManager) {
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * @inheritDoc
   */
  public function enqueue($field_name, $nodeList) {
    // entities that have a text area field with a link.
    // Just the body text field for now.
    if ($this->connection->schema()->tableExists('node__' . $field_name)) {
      $query = $this->connection->select('node__' . $field_name, 'b');
      // The entity may not be a node.
      $query->leftJoin('node', 'n', 'n.nid = b.entity_id');
      $query->leftJoin('node_field_data', 'd', 'n.nid = d.nid');
      $query->addField('b', 'entity_id');
      $query->addField('b', 'bundle');
      // Nodes only if they are published.
      $query->condition('d.status', 1);
      // Crudely find things that could be links.
      // Accurate link finding happens when the queue is processed.
      $query->condition($field_name . '_value', '%' . $query->escapeLike('<a') . '%', 'LIKE');

      // Optionally limit the result set
      $nids = $nodeList;

      if (!empty($nids)) {
        $query->condition('n.nid', $nids, 'IN');
      }

      $query->orderBy('b.entity_id', 'DESC');

      return $query->execute();
    }
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function fieldGetDom($id, $type, $fieldName) {
    $values = [];
    $value_field = $fieldName . '_value';
    if ($this->connection->schema()->tableExists('node__' . $fieldName)) {
      $query = $this->connection->select('node__' . $fieldName, 't');
      $query->addField('t', $value_field);
      $query->addField('t', 'revision_id');
      $query->condition('entity_id', $id);
      $query->condition('bundle', $type);
      $query->condition('delta', 0);
      $result = $query->execute()->fetchObject();
      if ($result) {
        $values['revision_id'] = $result->revision_id;
        // Convert all Windows and Mac newlines to a single newline, so filters only
        // need to deal with one possibility.
        // This has been copied from check_markup().
        $value = str_replace(["\r\n", "\r"], "\n", $result->{$value_field});
        $values['value'] = $value;
      }
    }
    return $values;
  }

  /**
   * @inheritDoc
   */
  public function fieldSave($id, $type, $fieldName, $revisionId, $updatedText) {
    $updated = 0;
    $entity_storage = $this->entityTypeManager->getStorage('node');
    $node = $entity_storage->load($id);
    /** @var $node \Drupal\node\NodeInterface */
    $node->set($fieldName, $updatedText);
    $node->save();
    $updated = 1;
//    foreach (['_', 'revision__'] as $table_type) {
//      $table = 'node_' . $table_type . $fieldName;
//      if ($this->connection->schema()->tableExists($table)) {
//        $updated += $this->connection->update($table)
//          ->fields([
//            $fieldName . '_value' => $updatedText
//          ])
//          ->condition('entity_id', $id)
//          ->condition('bundle', $type)
//          ->condition('revision_id', $revisionId)
//          // Hardcoded delta so only the first value of a multivalue field is used.
//          ->condition('delta', 0)
//          ->execute();
//      }
//    }
    return $updated;
  }
}
