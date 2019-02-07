<?php

namespace Drupal\een_opportunity_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\Row;
use Drupal\file\Entity\File;

/**
 * This plugin copies files from the Merlin XML source to Drupal image field
 *
 * @MigrateProcessPlugin(
 *   id = "pod_image_copy"
 * )
 */
class PodImageCopy extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if($value->name) {
      $filename_array = explode($value->name,'.');
      $extension = strtolower($filename_array[1]);
    }
    if (empty($value) || $extension == 'pdf') {
      // Skip this item if there's no URL.
      throw new MigrateSkipProcessException();
    }
    
    $file = file_save_data($value->data, file_default_scheme() . '://' . $value->name, FILE_EXISTS_RENAME);
    return $file->id();
  }

}
