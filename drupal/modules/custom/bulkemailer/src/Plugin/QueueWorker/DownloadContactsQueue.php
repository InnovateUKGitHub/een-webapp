<?php

namespace Drupal\bulkemailer\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use XMLReader;
use DOMDocument;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;


/**
 * Processes an individual POD for images and copies them.
 *
 * @QueueWorker(
 *   id = "download_contacts_queue",
 *   title = @Translation("Download Contacts Queue"),
 *   cron = {"time" = 90}
 * )
 */
class DownloadContactsQueue extends QueueWorkerBase {
  
  /**
   * {@inheritdoc}
   */
  public function processItem($data) {

      $salesforce = \Drupal::service('salesforce.client');
      $response = $salesforce->apiCall($data['nextRecordUrl']);
      $contacts = $response['records'];

      foreach($contacts as $contact){

          $name = $contact['FirstName'];
          if(!$name){
              $name = 'there'; // Some leads may not have names yet
          }

          try {
              db_insert('bulkemailer')
                  ->fields(array('email' => $contact['Email1__c'], 'cid' => $contact['Id'], 'uniquelink' => '', 'name' => $name))
                  ->execute();
          } catch(\Exception $e){

          }
      }


  }
  
}
