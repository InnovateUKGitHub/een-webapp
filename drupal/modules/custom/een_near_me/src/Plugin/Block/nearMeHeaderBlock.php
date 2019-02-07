<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\een_near_me\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides the Location Search Block.
 *
 * @Block(
 * id = "een_near_me_header",
 * admin_label = @Translation("Near Me Header"),
 * )
 */
class nearMeHeaderBlock extends BlockBase {
  public function build() {
    $content = '<div class="near-me-nav"><ul>' . 
        '<li><a href="#latest">Latest from EEN</a></li>' .
        '<li><a href="#advisers">Local Advisers</a></li>' .
        '<li><a href="#events">Events</a></li>' .
        '</ul></div>';
    $content .= '<div class="your-area">Your area';
    if(isset($_SESSION['county'])) {
      $content .= " " . $_SESSION['county'];
    }
    $content .= '</div>';
    
    return array(
      '#markup' => $content,
    );
  }
}
