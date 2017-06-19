<?php

namespace Drupal\een_custom_fields\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Plugin implementation of the 'een_copyright_image' field type.
 *
 * @FieldType(
 *   id = "een_copyright_image",
 *   label = @Translation("Copyrighted Image"),
 *   description = @Translation("This field stores the ID of an image file as an integer value."),
 *   category = @Translation("Reference"),
 *   default_widget = "image_copyright_image",
 *   default_formatter = "copyright_image",
 *   column_groups = {
 *     "file" = {
 *       "label" = @Translation("File"),
 *       "columns" = {
 *         "target_id", "width", "height"
 *       },
 *       "require_all_groups_for_translation" = TRUE
 *     },
 *     "alt" = {
 *       "label" = @Translation("Alt"),
 *       "translatable" = TRUE
 *     },
 *     "title" = {
 *       "label" = @Translation("Title"),
 *       "translatable" = TRUE
 *     },
 *     "copyright" = {
 *       "label" = @Translation("Copyright"),
 *       "translatable" = TRUE
 *     },
 *     "copyright_owner" = {
 *       "label" = @Translation("Copyright Owner"),
 *       "translatable" = TRUE
 *     },
 *   },
 *   list_class = "\Drupal\file\Plugin\Field\FieldType\FileFieldItemList",
 *   constraints = {"ReferenceAccess" = {}, "FileValidation" = {}}
 * )
 */
class CopyrightImageItem extends ImageItem {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return array(
      'default_image' => array(
        'uuid' => NULL,
        'alt' => '',
        'title' => '',
        'copyright' => '',
        'copyright_owner' => '',
        'width' => NULL,
        'height' => NULL,
      ),
        ) + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = array(
      'file_extensions' => 'png gif jpg jpeg',
      'alt_field' => 1,
      'alt_field_required' => 1,
      'title_field' => 0,
      'title_field_required' => 0,
      'copyright_field' => 0,
      'max_resolution' => '',
      'min_resolution' => '',
      'default_image' => array(
        'uuid' => NULL,
        'alt' => '',
        'title' => '',
        'copyright' => '',
        'copyright_owner' => '',
        'width' => NULL,
        'height' => NULL,
      ),
        ) + parent::defaultFieldSettings();

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    $schema['columns']['copyright'] = array(
      'description' => "Image source URL",
      'type' => 'varchar',
      'length' => 1024,
    );
    $schema['columns']['copyright_owner'] = array(
      'description' => "The name of the original copyright owner.",
      'type' => 'varchar',
      'length' => 1024,
    );
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['copyright'] = DataDefinition::create('string')
        ->setLabel(t('Copyright'))
        ->setDescription(t("Image source URL."));
    $properties['copyright_owner'] = DataDefinition::create('string')
        ->setLabel(t('Copyright Owner'))
        ->setDescription(t("The name of the original copyright owner."));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    // Get base form from FileItem.
    $element = parent::fieldSettingsForm($form, $form_state);

    $settings = $this->getSettings();

    $element['copyright_field'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable <em>Copyright</em> field'),
      '#default_value' => $settings['copyright_field'],
      '#description' => t('Enables Source URL if applicable.'),
      '#weight' => 11,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = parent::storageSettingsForm($form, $form_state, $has_data);
    return $element;
  }

}
