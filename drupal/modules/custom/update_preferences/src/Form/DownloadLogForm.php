<?php

namespace Drupal\update_preferences\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Sample UI to update a record.
 */
class DownloadLogForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'update_preferences_download_log';
  }

  /**
   * Sample UI to update a record.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


    // Wrap the form in a div.
    $form = [
      '#prefix' => '<div id="updateform">',
      '#suffix' => '</div>',
    ];
    // Add some explanatory text to the form.
    $form['message'] = [
      '#markup' => $this->t('Download log.'),
    ];


    $form['downloadcsv'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Download csv'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Process'),
    ];
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

      $filename = 'manage_preferences_log';
      $filepath = '/tmp/' . $filename.'.csv';
      $output = fopen($filepath, 'w+');

      $select = db_select('update_preferences', 'e');
      // Select these specific fields for the output.
      $select->addField('e', 'email');
      $select->addField('e', 'date');

      $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
      fputcsv($output, array("email", "date"));
      $i=0;
      foreach ($entries as $entry){
        $email = $entry['email'];
        $date = $entry['date'];
        fputcsv($output, array($email, $date));
        $i++;
      }
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
      header('Content-Length: ' . filesize($filepath));
      readfile($filepath);
      exit;
    }
  }

}
