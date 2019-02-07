<?php

namespace Drupal\een_opportunity_import\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of eenImportQueueStatus
 *
 * @author simonpotthast
 */
class eenImportQueueStatus extends ControllerBase {
  public function buildPage(Request $request) {
    // Get the queue
    $queue = \Drupal::queue('image_importer_queue');
    if($queue)
      $count = $queue->numberOfItems();
    
    $response = array(
      '#markup' => 'There are ' . (isset($count) ? $count : 'no') . ' items left to process in the queue.',
    );
    
    return $response;
  }
}
