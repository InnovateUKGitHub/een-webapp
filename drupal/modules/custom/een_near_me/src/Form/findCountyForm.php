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
 * County search form
 *
 * @author simonpotthast
 */
class findCountyForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'find_county_form';
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['search_query'] = array(
      '#type' => 'textfield',
      '#title' => 'Enter Town or Postcode',
    );
    $form['submit'] = array (
      '#type' => 'submit',
      '#value' => t('Search'),
    );
    
    $search_results = $_SESSION['town_search_results'];
    if($search_results) {
      $content = '<table><tr><td>Town</td><td>County</td><td>&nbsp;</td></tr>';
      foreach($search_results as $key => $value) {
        $content .= '<tr><td>' . $value->name . '</td>' .
            '<td>' . $value->county . '</td>' .
            '<td><a href="/ajax/user/set-county/' . $value->county . '">Select</a></td></tr>';
      }
      $content .= '</table>';
      
      $form['town_options'] = array(
        '#type' => 'item',
        '#markup' => $content,
      );
      unset($_SESSION['town_search_results']);
    }
    
    $form['find_my_location'] = array(
      '#type' => 'item',
      '#markup' => '<div class="button findMe">Find my location</div>',
      '#suffix' => '<p id="locate-status"></p>',
    );
    
    $form['close_form'] = array(
      '#type' => 'item',
      '#markup' => '<div class="button">Close</div>',
    );
    
    $form['#attached']['library'][] = 'een_near_me/nearme';

    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $search_string = $form_state->getValue('search_query');
    if($this->isPostcode($search_string)) {
      $town_search_results = $this->postcodeSearch($this->getPostCodeSector($search_string));
    }
    else {
      $town_search_results = $this->townSearch($search_string);
    }
    $_SESSION['town_search_results'] = $town_search_results;
  }
  
  /**
   * Search for a town based on the input string
   * 
   * @param string $search_string
   * @return array
   */
  public function townSearch($search_string) {
    $query = db_select('uk_towns', 't');
    $query->condition('name', db_like($search_string) . '%', 'like');
    $query->fields('t', array('id', 'name', 'postcode_sector', 'county'));
    $query->distinct();
    $result = $query->execute();
    $towns = $result->fetchAllAssoc('id');
    return $towns;
  }
  
  /**
   * Checks if postcode is valid
   * 
   * @param string $postcode
   * @return boolean
   */
  public function isPostcode($postcode)
  {
    $postcode = strtoupper(str_replace(' ','',$postcode));
    if(preg_match("/(^[A-Z]{1,2}[0-9R][0-9A-Z]?[\s]?[0-9][ABD-HJLNP-UW-Z]{2}$)/i",$postcode) || preg_match("/(^[A-Z]{1,2}[0-9R][0-9A-Z]$)/i",$postcode))
    {    
      return true;
    }
    else
    {
      return false;
    }
  }
  
  /**
   * Helper function to get postcode sector
   * 
   * @param string $postcode
   * @return string
   */
  public function getPostCodeSector($postcode) {
    // split the string into an array
    $string_array = split(' ', $postcode);
    
    // concatenate array[0] . space . 1st char of array[1]
    $postcode_sector  = $string_array[0] . ' ' . substr($string_array[1], 0, 1);
    
    return $postcode_sector;
  }

  /**
   * Search for town by postcode
   * 
   * @param string $postcode
   * @return array
   */
  public function postcodeSearch($postcode) {
    $query = db_select('uk_towns', 't');
    $query->condition('t.postcode_sector', $postcode, '=');
    $query->fields('t', array('id', 'name', 'postcode_sector', 'county'));
    $query->distinct();
    $result = $query->execute();
    $towns = $result->fetchAllAssoc('id');
    if(count($towns) === 0) {
      // We didn't find a result so we need to widen the search
      // We'll do this by removing the post code sector number
      $wider_postcode = substr($postcode, 0, mb_strlen($postcode)-1);
      $wider_query = db_select('uk_towns', 't');
      $wider_query->condition('postcode_sector', db_like($wider_postcode) . '%', 'like');
      $wider_query->fields('t', array('id', 'name', 'postcode_sector', 'county'));
      $wider_query->distinct();
      $wider_result = $wider_query->execute();
      $towns = $wider_result->fetchAllAssoc('id');
      return $towns;
    }
    else {
      return $towns;
    }
  }
}
