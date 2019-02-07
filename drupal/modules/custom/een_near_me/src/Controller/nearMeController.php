<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\een_near_me\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\views\Views;
use GuzzleHttp\Client;

/**
 * Provides the page and all Near Me Functions
 *
 * @author simonpotthast
 */
class nearMeController extends ControllerBase {

  /**
   * Constructs the Near Me Landing Page
   * 
   * @param Request $request
   * @return type
   */
  public function buildPage(Request $request) {    
    // Create the content variable
    $content = '';
    
    // Check if a county has been set for the current session
    if(isset($_SESSION['county'])) {
      $county[] = $_SESSION['county'];
    }
        
    // Add the content view
    $content .= $this->addView('near_me_content', 'block_1', $county);
    
    // Add the useful contacts view
    $content .= $this->addView('near_me_useful_contacts', 'block_1', $county);

    // Add the advisers view
    $content .= $this->addView('near_me_advisers', 'block_1', $county);
    
    // Add the events view
    $content .= $this->addView('near_me_events', 'block_1', $county);
    
    // Stick everything in a render array
    $response = array(
      '#markup' => $content,
    );
    
    return $response;
  }
  
  /**
   * Helper function to render a view with args
   * 
   * @param string $view_id
   * @param string $display
   * @param array $args
   * @return string
   */
  public function addView($view_id, $display, array $args = NULL) {
    $view = Views::getView($view_id);
    $view->setDisplay($display);
    if(isset($args)) {
      $view->setArguments($args);
    }
    $view->preExecute();
    $view->execute();
    $view_render_array = $view->render();
    $rendered_view = render($view_render_array);
    return $rendered_view;
  }
  
  /**
   * Helper function to look up a postcode from lat/lon
   * 
   * @param float $lat
   * @param float $long
   * @return string
   */
  public function latLongToPostCode($lat, $long) {
    $client = new Client();
    $url = 'https://api.postcodes.io/postcodes/?lon=' . $long . '&lat=' . $lat;
    $res = $client->request('GET', $url);
    $body = $res->getBody();
    $content = \GuzzleHttp\json_decode($body->getContents());
    if($content->result[0]) {
      $postcode = $content->result[0]->postcode;
      return $postcode;
    }
  }
  
  /**
   * Helper function to convert postcode to County 
   * 
   * @param string $postcode
   * @return string
   */
  public function postCodeToCounty($postcode) {
    $query = db_select('uk_towns', 't');
    $query->condition('t.postcode_sector', $postcode, '=');
    $query->fields('t', array('county'));
    $query->distinct();
    $result = $query->execute();
    foreach ($result as $record) {
      $county = $record->county;
    }
    if(!isset($county)) {
      // We didn't find a result so we need to widen the search
      // We'll do this by removing the post code sector number
      $wider_postcode = substr($postcode, 0, mb_strlen($postcode)-1);
      $wider_query = db_select('uk_towns', 't');
      $wider_query->condition('postcode_sector', db_like($wider_postcode) . '%', 'like');
      $wider_query->fields('t', array('county'));
      $wider_query->distinct();
      $wider_result = $wider_query->execute();
      foreach ($wider_result as $wider_record) {
        $county = $wider_record->county;
      }
      return $county;
    }
    else {
      return $county;
    }
  }
    
  /**
   * Helper function to convert Town to County
   * 
   * @param string $town
   * @return string
   */
  public function townToCounty($town) {
    $query = db_select('uk_towns', 't');
    $query->condition('t.name', $town, '=');
    $query->fields('t', array('county'));
    $query->distinct();
    $result = $query->execute();
    foreach ($result as $record) {
      $county = $record->county;
    }
    return $county;
  }
  
  /**
   * Search for towns from input string
   * 
   * @param string $search_string
   * @return array
   */
  public function townSearch($search_string) {
    $query = db_select('uk_towns', 't');
    $query->condition('name', db_like($search_string) . '%', 'like');
    $query->fields('t', array('id', 'name'));
    $query->distinct();
    $result = $query->execute();
    $towns = $result->fetchAllAssoc('id');
    return $towns;
  }
  
  /**
   * Helper function to shorten a postcode to postcode sector
   * 
   * @param type $postcode
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
   * Set county session variable
   * 
   * @param type $county
   * @param Request $request
   * @return Redirect
   */
  public function setCounty($county, Request $request) {
    $_SESSION['county'] = $county;
    return $this->redirect('een_near_me.landing_page');
  }

  /**
   * Set county from HTML5 Geolocation API
   * 
   * @param float $lat
   * @param float $long
   * @param Request $request
   * @return Redirect
   */
  public function setCountyFromClient($lat, $long, Request $request) {
    $postcode = $this->latLongToPostCode($lat, $long);
    $county = $this->postCodeToCounty($this->getPostCodeSector($postcode));
    $_SESSION['county'] = $county;
    return $this->redirect('een_near_me.landing_page');
  }

  /**
   * Helper function to clear county session variable
   * 
   * @param Request $request
   * @return Redirect
   */
  public function clearCounty(Request $request) {
    if(isset($_SESSION['county'])) {
      unset($_SESSION['county']);
    }
    return $this->redirect('een_near_me.landing_page');
  }
}
