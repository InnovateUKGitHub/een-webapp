<?php

/* 
 * Batch processing functions for submissions
 */

/**
 * Worker Thread
 * 
 * @param type $row
 * @param type $statuses
 * @param type $context
 */
function batchWorker($row, &$context) {
  // Load the row into the fields
  $fields = array(
    'id' => $row[0],
    'name' => $row[1],
    'county' => $row[2],
    'country' => $row[3],
    'grid_reference' => $row[4],
    'easting' => $row[5],
    'northing' => $row[6],
    'latitude' => $row[7],
    'longitude' => $row[8],
    'postcode_sector' => $row[9],
    'local_government_area' => $row[10],
    'nuts_region' => $row[11],
    'type' => $row[12],
  );
  db_insert('uk_towns')
    ->fields($fields)
    ->execute();
    
  $context['results'][] = $row[1] . ' : processed';
  $context['message'] = t('Processing @id', array('@id' => $row[0]));
}

/**
 * Batch finished callback
 * 
 * @param type $success
 * @param type $results
 * @param type $operations
 */
function batchFinishedCallback($success, $results, $operations) {
  // The 'success' parameter means no fatal PHP errors were detected. All
  // other error management should be handled using 'results'.
  if ($success) {
    $message = \Drupal::translation()->formatPlural(count($results), 'One row processed.', '@count rows processed.');
  }
  else {
    $message = t('Finished with an error.');
  }
  drupal_set_message($message);
  // Providing data for the redirected page is done through $_SESSION.
  foreach ($results as $result) {
    $items[] = t('Loaded node %title.', array('%title' => $result));
  }
  $_SESSION['my_batch_results'] = $items;
}
