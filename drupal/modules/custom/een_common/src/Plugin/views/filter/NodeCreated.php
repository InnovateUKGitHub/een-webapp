<?php

namespace Drupal\een_common\Plugin\views\filter;
 
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use DateTime;
use DatePeriod;
use DateInterval;

/**
 * Filters by given list of node title options.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("een_common_node_created_filter")
 */
class NodeCreated extends FilterPluginBase {
  protected $month_values = [];
  
  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    
    $options['value'] = array(
      'contains' => array(
        'min' => array('default' => 0),
        'max' => array('default' => time()),
      ),
    );
    $options['operator']['default'] = 'between';
    return $options;
  }
    
  /**
   * Provide a selection of months and years
   * We want the last 5 months and a 'more' option
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);
    $form['value']['#tree'] = TRUE;
    
    $startDate = new DateTime('first day of this month - 4 months');
    $endDate   = new DateTime('last day of this month');
    $interval  = new DateInterval('P1M'); // P1M => 1 month per iteration

    $datePeriod = new DatePeriod($startDate, $interval, $endDate);

    foreach($datePeriod as $dt) {
      $key = $dt->format('m-Y');
      $month_options[$key] = $dt->format('M Y');
    }
    $month_options = array_reverse($month_options);
    
    $month_options['older'] = 'Older';
        
    $form['months'] = array(
      '#type' => 'checkboxes',
      '#options' => $month_options,
      '#title' => 'Archive',
    );
    
    $form['value']['min'] = array(
      '#type' => 'hidden',
      '#default_value' => $this->value['min'],
    );
    $form['value']['max'] = array(
      '#type' => 'hidden',
      '#default_value' => $this->value['max'],
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitExposed(&$form, FormStateInterface $form_state) {
    parent::submitExposed($form, $form_state);
    // unset previous values
    $month_values = $form_state->getValue('months');
    $this->month_values = $month_values;
  } 
  
  /**
   * {@inheritdoc}
   */
  public function query() {  
    $this->value['max'] = time();
    parent::query();
    // get the selected options from 'months'
    $month_values = $this->month_values;
    // Loop through them and add start and end timestamps to value
    foreach ($month_values as $key => $value) {
      if($key === $value) {
        if($key === 'older') {
          $endDate = new DateTime('last day of this month - 5 months');
          $endTimestamp = $endDate->getTimestamp();
          $this->query->addWhere($this->options['group'], 'node_field_data.created', array(0, $endTimestamp), 'BETWEEN');
        }
        else {
          // split into month and year
          $date_array = explode('-', $value);
          // Get timestamps
          $timestamps = $this->getMonthStartAndEndTimestamps($date_array[0], $date_array[1]);
          $this->query->addWhere($this->options['group'], 'node_field_data.created', array($timestamps['start'], $timestamps['end']), 'BETWEEN');
        }
      }
    }
  }

  /**
   * force exposed input
   */
  public function acceptExposedInput($input) {
    if (empty($this->options['exposed'])) {
      return TRUE;
    }
    return TRUE;
  }
  
  /**
   * Sets the filter criteria parenthical on the view edit screen
   * 
   * @return string
   */
  public function adminSummary() {
    if ($this->isAGroup()) {
      return $this->t('grouped');
    }
    if (!empty($this->options['exposed'])) {
      return $this->t('exposed');
    }
    
    return $this->t('selected');
  }
  
  /**
   * Helper function to get timestamps
   * 
   * @param string $month
   * @param string $year
   * @return array
   */
  public function getMonthStartAndEndTimestamps($month, $year) {
    $dates = array();
    
    $dates['start'] = mktime(0, 0, 1, $month, 1, $year);
    $dates['end'] = mktime(23, 59, 59, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year),$year);
    
    return $dates;
  }
}