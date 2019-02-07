<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\een_opportunity_import\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use DateTime;
use DateInterval;
use DatePeriod;

/**
 * Provides the ability to update the base UK Towns geography
 *
 * @author simonpotthast
 */
class eenImportPodImagesForm extends FormBase {
  public function getFormId() {
    return 'import_pod_images_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['import_from'] = array(
      '#title' => t('Begin import from this data'),
      '#type' => 'date',
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
    // Get all possible dates between the start date and today
    $start = new DateTime($form_state->getValue('import_from'));
    $end = new DateTime('NOW');
    $date_array = $this->getDatesFromRange($start->format('Ymd'), $end->format('Ymd'));

    // Get the queue
    $queue = \Drupal::queue('image_importer_queue');

    // Loop through the date array and create items
    foreach ($date_array as $value) {
      $interval = new DateInterval('P1D');
      $end_day = new DateTime($value);
      $end_day->add($interval);
      $queue->createItem(['day' => $value, 'end_day' => $end_day->format('Ymd')]);
    }
    drupal_set_message('Cron job has been created.');
  }

  /**
  * Generate an array of string dates between 2 dates
  *
  * @param string $start Start date
  * @param string $end End date
  * @param string $format Output format (Default: Ymd)
  *
  * @return array
  */
  function getDatesFromRange($start, $end, $format = 'Ymd') {
    $array = array();
    $interval = new DateInterval('P1D');

    $realEnd = new DateTime($end);
    $realEnd->add($interval);

    $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

    foreach($period as $date) {
        $array[] = $date->format($format);
    }

    return $array;
  }
}
