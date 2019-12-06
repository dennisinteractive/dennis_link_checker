<?php

namespace Drupal\dennis_link_checker;

/**
 * Interface CheckerQueriesManagerInterface
 * @package Drupal\dennis_link_checker
 */
interface CheckerQueriesManagerInterface {

  /**
   * @param $field_name
   * @param $nodeList
   * @return bool|\Drupal\Core\Database\StatementInterface|null
   */
  public function enqueue($field_name, $nodeList);


  /**
   * @param $id
   * @param $type
   * @param $fieldName
   * @return array
   */
  public function fieldGetDom($id, $type, $fieldName);

  /**
   * @param $id
   * @param $type
   * @param $fieldName
   * @param $revisionId
   * @param $updatedText
   * @return \Drupal\Core\Database\StatementInterface|int|null
   */
  public function fieldSave($id, $type, $fieldName, $revisionId, $updatedText);

}
