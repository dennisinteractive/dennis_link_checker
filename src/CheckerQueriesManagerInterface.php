<?php

namespace Drupal\dennis_link_checker;

/**
 * Interface CheckerQueriesManagerInterface.
 *
 * @package Drupal\dennis_link_checker
 */
interface CheckerQueriesManagerInterface {

  /**
   * Enqueue.
   *
   * @param string $field_name
   *   Field name.
   * @param array $nodeList
   *   Node list.
   *
   * @return bool|\Drupal\Core\Database\StatementInterface|null
   *   Return value.
   */
  public function enqueue($field_name, array $nodeList);

  /**
   * Get the field dom.
   *
   * @param string $id
   *   Id.
   * @param string $type
   *   Type.
   * @param string $fieldName
   *   Field name.
   *
   * @return array
   *   Returns an array.
   */
  public function fieldGetDom($id, $type, $fieldName);

  /**
   * Save the field.
   *
   * @param string $id
   *   Id.
   * @param string $type
   *   Type.
   * @param string $fieldName
   *   Field name.
   * @param string $revisionId
   *   Revision id.
   * @param string $updatedText
   *   Updated text.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *   Return value.
   */
  public function fieldSave($id, $type, $fieldName, $revisionId, $updatedText);

}
