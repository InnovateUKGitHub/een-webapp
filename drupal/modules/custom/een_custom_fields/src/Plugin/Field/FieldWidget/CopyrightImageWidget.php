<?php

namespace Drupal\een_custom_fields\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image_widget_crop\Plugin\Field\FieldWidget\ImageCropWidget;

/**
 * Plugin implementation of the 'image_copyright_image' widget.
 *
 * @FieldWidget(
 *   id = "image_copyright_image",
 *   label = @Translation("Extra Copyright Field"),
 *   field_types = {
 *     "een_copyright_image"
 *   }
 * )
 */
class CopyrightImageWidget extends ImageCropWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $field_settings = $this->getFieldSettings();
    $element['#copyright_field'] = $field_settings['copyright_field'];
    $element += array(
      '#element_validate' => array(
        array($this, 'validate'),
      ),
    );

    return $element;
  }

  /**
   * Form API callback: Processes a image_copyright_image field element.
   *
   * Expands the image_copyright_image type to include the copyright fields.
   *
   * This method is assigned as a #process callback in formElement() method.
   *
   * @param $element
   * @param FormStateInterface $form_state
   * @param $form
   * @return mixed
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $item = $element['#value'];
    $item['fids'] = $element['fids']['#value'];

    $element['copyright'] = array(
      '#type' => 'textfield',
      '#title' => t('Image Source'),
      '#default_value' => isset($item['copyright']) ? $item['copyright'] : '',
      '#description' => t('Client company name or	image library	url: eg https://www.shutterstock.com/image-illustration/data-protection-concept-drawn-on-dark-485957521'),
      '#maxlength' => 1024,
      '#weight' => -11,
      '#access' => (bool) $item['fids'] && $element['#copyright_field'],
    );

    $element['copyright_owner'] = array(
      '#type' => 'textfield',
      '#title' => t('Copyright Owner'),
      '#default_value' => isset($item['copyright_owner']) ? $item['copyright_owner'] : '',
      '#description' => t('Client company name or image	library	name:	eg Shutterstock. Text in	this field will display on the page.'),
      '#maxlength' => 1024,
      '#weight' => -11,
      '#access' => (bool) $item['fids'] && $element['#copyright_field'],
    );

    return parent::process($element, $form_state, $form);
  }

  /**
   * Custom validation rules for source URL
   *
   * @param $element
   * @param FormStateInterface $form_state
   */
  public function validate($element, FormStateInterface $form_state) {
    if (isset($element['#value']['copyright'])) {
      $value = $element['#value']['copyright'];
      if ((mb_strlen($value)) < 6) {
        $form_state->setError($element['copyright'], t('Source URL must be longer than 6 characters.'));
      }
    }
  }

}
