<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\een_near_me\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the ability to update the base UK Towns geography
 *
 * @author simonpotthast
 */
class eenImportTownsListForm extends FormBase {
  public function getFormId() {
    return 'import_towns_list_form';
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['import'] = array(
      '#title' => t('Import Towns List CSV'),
      '#type' => 'managed_file',
      '#description' => t('The uploaded csv will be imported and all existing data will be removed.'),
      '#upload_location' => 'public://',
      '#upload_validators' => array(
        'file_validate_extensions' => array('csv'),
      ),
    );
    $form['submit'] = array (
      '#type' => 'submit',
      '#value' => t('Import'),
    );
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Load the CSV
    $fid = $form_state->getValue('import');
    $uri = db_query("SELECT uri FROM {file_managed} WHERE fid = :fid", array(
      ':fid' => $fid[0],
    ))->fetchField();
    if(!empty($uri)) {
      if(file_exists(drupal_realpath($uri))) { 
        // Open the csv
        $handle = fopen(drupal_realpath($uri), "r");
        // Create the batch
        while (($data = fgetcsv($handle, 0, ',', '"')) !== FALSE) {
          if($data[0] !== 'id')
            $ops[] = array('batchWorker', array($data));
        }
        $batch = array(
          'title' => t('Importing'),
          'operations' => $ops,
          'finished' => 'batchFinishedCallback',
          'file' => drupal_get_path('module', 'een_near_me') . '/includes/batch_functions.inc',
        );
        
        // truncate the existing table
        db_query('TRUNCATE {uk_towns}')->execute();

        // Set the batch going
        batch_set($batch);
      }
    }
    else {
      drupal_set_message(t('There was an error uploading your file. Please contact a System administator.'), 'error');
    }
  }
}
