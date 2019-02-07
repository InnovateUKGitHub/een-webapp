<?php

namespace Drupal\bulkemailer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\bulkemailer\BulkEmailerStorage;

/**
 * Sample UI to update a record.
 */
class DownloadContactsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bulkemailer_update_form';
  }

  /**
   * Sample UI to update a record.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

      $form['message'] = [
          '#markup' => $this->t('<ul>
            <li>Step 1: Click on Download contacts: step 1</li>
            <li>Step 2: Visit <a href="/admin/config/system/queue-ui"> here</a> and batch process <strong>Download Contacts Queue</strong> </li>
            <li>Step 3: Go <a href="/admin/bulkemailer/addtoqueue">here</a> to download the contact details with the special links</li>
            </ul><br /><br />'),
      ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Download contacts: step 1'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {


      $query = '
SELECT Id, Email1__c, Contact_Status__c, FirstName
FROM Contact';


      $salesforce = \Drupal::service('salesforce.client');
      $response = $salesforce->apiCall('query?q=' . urlencode($query));
      $nextRecordUrl = substr($response['nextRecordsUrl'], 0, -5);

      db_truncate('bulkemailer')->execute();

      $query = '
SELECT COUNT()
FROM Contact';

      $salesforce = \Drupal::service('salesforce.client');
      $response = $salesforce->apiCall('query?q=' . urlencode($query));

      $totalRecords =  $response['totalSize'];
      $offsetCount = 2000;
      $howManyInTheQueue = round($totalRecords / $offsetCount) -1;

      $queue = \Drupal::queue('download_contacts_queue');
      for ($x = 0; $x <= $howManyInTheQueue; $x++) {
         $queue->createItem(['nextRecordUrl' => $nextRecordUrl."-".($x * $offsetCount)]);
      }
  }


}
