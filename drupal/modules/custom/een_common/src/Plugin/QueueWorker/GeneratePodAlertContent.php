<?php
/**
 * @file
 * Contains \Drupal\simple_mail\Plugin\QueueWorker\SimpleMailSendQueuedMail.
 */

namespace Drupal\een_common\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * @QueueWorker(
 *   id = "pod_alert_notify_queue_contact",
 *   title = @Translation("POD ALERT Generate email content"),
 *   cron = {"time" = 60}
 * )
 */
class GeneratePodAlertContent extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($message)
  {

    $httpService = new \Drupal\service_connection\Service\HttpService();
    $oppService = new \Drupal\opportunities\Service\OpportunitiesService($httpService);
    $contactService = new \Drupal\een_common\Service\ContactService($httpService);
    $requestObject = new \Symfony\Component\HttpFoundation\Request();

    $contactId = $message['contactId'];
    $alerts = $message['results'];
    $i=0;
    if($contactId){

      $batch[$i]['contactId'] = $contactId;
      $batch[$i]['opportunities'] = [];

      foreach($alerts as $alert){
        $search = str_replace('/', ' ', $alert['Search_Term__c']);
        $types = array();
        if($alert['Business_Offer__c'] == 1) { $types[] = 'BO'; }
        if($alert['Business_Request__c'] == 1) { $types[] = 'BR'; }
        if($alert['R_and_D_Request__c'] == 1) { $types[] = 'RDR'; }
        if($alert['Technology_Offer__c'] == 1) { $types[] = 'TO'; }
        if($alert['Technology_Request__c'] == 1) { $types[] = 'TR'; }
        $countries = array($alert['Country__c']);

        $opportunities = $oppService->getOpportunitiesForAlerts($requestObject, $search, $types, $countries, 1, 50);

        if($opportunities['total'] > 0){
          if($opportunities){
            foreach($opportunities['results'] as $result){
              $batch[$i]['opportunities'][$result['id']] = $result;
            }
          }
        }
      }
    }

    foreach ($batch as $finalBatch){
      $validOpportunities = getValidOpportunities($finalBatch['opportunities']);

      if(count($validOpportunities) > 0) {

        $contact = $contactService->getContactFromId($finalBatch['contactId']);
        $emailAddress = $contact['Email1__c'];
        $html = $oppService->createEmailAlertContent($validOpportunities);

        // Queue the email - look to een_common/src/Plugin/QueueWorker for mail behaviour
        $queue = \Drupal::queue('pod_alert_notify_queue', TRUE);
        $item = array(
          'from' => array('een@aerian.com' => 'Enterprise Europe Network, United Kingdom'),
          'to' => $emailAddress,
          'body' => $html,
          'contactid' => sha1($emailAddress.$contact['Id'])
        );
        $queue->createItem($item);
      }
    }


  }
}
