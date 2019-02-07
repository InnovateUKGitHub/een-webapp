<?php

namespace Drupal\een_near_me\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides the Location Search Block.
 *
 * @Block(
 * id = "een_near_me_find_county_block",
 * admin_label = @Translation("Find County Search Form"),
 * )
 */
class findCountyBlock extends BlockBase{

  /**
   * Builds and returns the renderable array for this block plugin.
   *
   * If a block should not be rendered because it has no content, then this
   * method must also ensure to return no content: it must then only return an
   * empty array, or an empty array with #cache set (with cacheability metadata
   * indicating the circumstances for it being empty).
   *
   * @return array
   *   A renderable array representing the content of the block.
   *
   * @see \Drupal\block\BlockViewBuilder
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\een_near_me\Form\findCountyForm');
    return $form;
  }

}
