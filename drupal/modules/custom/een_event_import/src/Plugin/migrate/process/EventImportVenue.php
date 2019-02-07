<?php

namespace Drupal\een_event_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\Row;
use Drupal\Component\Serialization\Json;

/**
 * This plugin performs a lookup to Google Geolocation API to get the lat/lon of a city
 *
 * @MigrateProcessPlugin(
 *   id = "event_venue_lookup"
 * )
 */
class EventImportVenue extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $source = $row->getSource();
    if (empty($source['venue'])) {
      throw new MigrateSkipProcessException();
    }
    
    // Call the EventBrite API to get venue
    $request_url = 'https://www.eventbriteapi.com/v3/venues/' . $source['venue'] . '/?token=' . $this->configuration['token'];
    $response = Json::decode(\Drupal::httpClient()->request('GET', $request_url)->getBody());

    if(isset($response['address'])) {
      // Return the correct part of the address
      switch($this->configuration['type']) {
        case 'lat':
          return $response['address']['latitude'];
        case 'lng':
          return $response['address']['longitude'];
        case 'city':
          return $response['address']['city'];
        case 'country':
          return \Locale::getDisplayRegion('-' . $response['address']['country'], 'en');
        case 'country_code':
          return $response['address']['country'];
      }
    }
  }
}
