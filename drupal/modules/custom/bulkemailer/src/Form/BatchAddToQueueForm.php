<?php

namespace Drupal\bulkemailer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\bulkemailer\BulkEmailerStorage;

/**
 * Sample UI to update a record.
 */
class BatchAddToQueueForm extends FormBase {

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


    $select = db_select('bulkemailer', 'e');
    // Select these specific fields for the output.
    $select->addField('e', 'pid');
    $select->addField('e', 'name');
    $select->addField('e', 'surname');
    $select->addField('e', 'email');
    $select->addField('e', 'emailsent');
    // Filter only persons named "John".
    $select->condition('e.emailqueued', '0');
    $select->condition('e.emailsent', '0');
    // Make sure we only get items 0-49, for scalability reasons.

    $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

    $count = count($entries);
    $rows = 0;
    if($count > 1000){
      $rows = round($count  /1000);
    }

    $batches = [];
    for ($i = 0; $i <= $rows; $i++) {
      $batches[$i] =  ($i * 1000 + 1).' - '. ($i * 1000 + 1000);
    }

    // Wrap the form in a div.
    $form = [
      '#prefix' => '<div id="updateform">',
      '#suffix' => '</div>',
    ];
    // Add some explanatory text to the form.
    $form['message'] = [
      '#markup' => $this->t('Adds manage preferences to the queue.'),
    ];


    $form['downloadcsv'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Download csv'),
    ];


    $form['email'] = [
        '#type' => 'email',
        '#title' => $this->t('Send test email (this will cancel any of the options selected below)'),
    ];



    /*$form['batch'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('<h2>Batch process email</h2>'),
        '#options' => $batches
    ];*/

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Process'),
    ];
    return $form;
  }

  /**
   * AJAX callback handler for the pid select.
   *
   * When the pid changes, populates the defaults from the database in the form.
   */
  public function updateCallback(array $form, FormStateInterface $form_state) {
    // Gather the DB results from $form_state.
    $entries = $form_state->getValue('entries');
    // Use the specific entry for this $form_state.
    $entry = $entries[$form_state->getValue('pid')];
    // Setting the #value of items is the only way I was able to figure out
    // to get replaced defaults on these items. #default_value will not do it
    // and shouldn't.
    foreach (['name', 'surname', 'age'] as $item) {
      $form[$item]['#value'] = $entry->$item;
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {


  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    if($_POST['downloadcsv']){


     header('Content-Type: text/csv; charset=utf-8');
     header('Content-Disposition: attachment; filename=contacts-export.csv');

      $output = fopen('php://output', 'w+');

      $select = db_select('bulkemailer', 'e');
      // Select these specific fields for the output.
      $select->addField('e', 'pid');
      $select->addField('e', 'name');
      $select->addField('e', 'surname');
      $select->addField('e', 'email');
      $select->addField('e', 'emailsent');
      $select->addField('e', 'cid');

      $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

      fputcsv($output, array("email address", "firstname", "link"));
      $i=0;

      foreach ($entries as $entry){
        $email = $entry['email'];
        $name = $entry['name'];
        $contactId = $entry['cid'];

        $link =  'https://www.enterprise-europe.co.uk/manage-your-preferences?email='.$email.'&t='.sha1($email.$contactId);
        fputcsv($output, array($email, $name, $link));
        $i++;
      }
      db_truncate('bulkemailer')->execute();
      exit;
    }

    //send email and doesn't process the rest
    if($_POST['email'] && !empty($_POST['email']))
    {
      $email = $_POST['email'];
      $contactId = $this->getContactId($email);
      $name = 'there';

      $api_key = \Drupal::config('opportunities.settings')->get('api_key');
      $notifyClient = new \Alphagov\Notifications\Client([
          'apiKey' => $api_key,
          'httpClient' => new \Http\Adapter\Guzzle6\Client
      ]);
      $email_template_key = '905b3768-ee46-4e3e-bf1c-4fb1be516c67';
      $parameters = [];
      $parameters['firstname'] = $name;
      $parameters['link'] = 'https://www.enterprise-europe.co.uk/manage-your-preferences?email='.$email.'&t='.sha1($email.$contactId);

      try {
        $response = $notifyClient->sendEmail($email, $email_template_key, $parameters);
      } catch (NotifyException $e) {

      }
      drupal_set_message('Test email has been sent to '.$email);
      return true;
    }
  }


  private function getContactId($email)
  {
    $salesforce = \Drupal::service('salesforce.client');
    $query = '
SELECT Id, Email, Email1__c
FROM Contact
WHERE Email1__c = \'' . $email . '\'
';

    $result = $salesforce->apiCall('query?q=' . urlencode($query));

    try {

      if(isset($result['records'][0]) && $result['records'][0]['Email1__c'] == $email){
        return $result['records'][0]['Id'];
      }
    } catch(\Exception $e){

    }
  }

}
