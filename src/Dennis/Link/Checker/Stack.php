<?php
/**
 * @file
 * Stack.php
 */
namespace Dennis\Link\Checker;
use SystemQueue;

/**
 * Class Stack
 *
 * An implementation of a stack data structure; last in first out
 */
class Stack extends SystemQueue {

  /**
   * @inheritdoc
   */
  public function claimItem($lease_time = 30) {
    while (TRUE) {
      $item = db_query_range('SELECT data, item_id FROM {queue} q WHERE 
          expire = 0 AND name = :name ORDER BY created DESC', 0, 1,
        array(':name' => $this->name))->fetchObject();
      if ($item) {
        $update = db_update('queue')
          ->fields(array(
            'expire' => time() + $lease_time,
          ))
          ->condition('item_id', $item->item_id)
          ->condition('expire', 0);
        // If there are affected rows, this update succeeded.
        if ($update->execute()) {
          $item->data = unserialize($item->data);
          return $item;
        }
      }
      else {
        // No items currently available to claim.
        return FALSE;
      }
    }
  }
}
