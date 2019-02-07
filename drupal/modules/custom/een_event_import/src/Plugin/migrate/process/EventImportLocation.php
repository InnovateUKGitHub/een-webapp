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
 *   id = "event_location_lookup"
 * )
 */
class EventImportLocation extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $source = $row->getSource();
    if (empty($source['city'])) {
      throw new MigrateSkipProcessException();
    }
    // Call Google Geolocation API with city and country
    $query = $source['city'] . ',' . $source['country'] . '&types=(cities)';
    $response = $this->geocode($query);
    
    if($response) {
      // Check whether we are returning lat/lon
      if($this->configuration['type'] === 'lat') {
        return $response['location']['lat'];
      }
      else {
        return $response['location']['lng'];
      }
    }
  }
  
  /**
   * Searches Google Places API for a given address
   */
  public function geocode($address) {

    if (empty($address)) {
      return FALSE;
    }
    $request_url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?input=' . $address;

    $config = \Drupal::config('geolocation.settings');

    if (!empty($config->get('google_map_api_key'))) {
      $request_url .= '&key=' . $config->get('google_map_api_key');
    }
    if (!empty($this->configuration['component_restrictions']['country'])) {
      $request_url .= '&components=country:' . $this->configuration['component_restrictions']['country'];
    }
    if (!empty($config->get('google_map_custom_url_parameters')['language'])) {
      $request_url .= '&language=' . $config->get('google_map_custom_url_parameters')['language'];
    }

    try {
      $result = Json::decode(\Drupal::httpClient()->request('GET', $request_url)->getBody());
    }
    catch (RequestException $e) {
      watchdog_exception('geolocation', $e);
      return FALSE;
    }

    if (
      $result['status'] != 'OK'
      || empty($result['predictions'][0]['place_id'])
    ) {
      return FALSE;
    }

    try {
      $details_url = 'https://maps.googleapis.com/maps/api/place/details/json?placeid=' . $result['predictions'][0]['place_id'];

      if (!empty($config->get('google_map_api_key'))) {
        $details_url .= '&key=' . $config->get('google_map_api_key');
      }
      $details = Json::decode(\Drupal::httpClient()->request('GET', $details_url)->getBody());

    }
    catch (RequestException $e) {
      watchdog_exception('geolocation', $e);
      return FALSE;
    }

    if (
      $details['status'] != 'OK'
      || empty($details['result']['geometry']['location'])
    ) {
      return FALSE;
    }

    return [
      'location' => [
        'lat' => $details['result']['geometry']['location']['lat'],
        'lng' => $details['result']['geometry']['location']['lng'],
      ],
      // TODO: Add viewport or build it if missing.
      'boundary' => [
        'lat_north_east' => empty($details['result']['geometry']['viewport']) ? $details['result']['geometry']['location']['lat'] + 0.005 : $details['result']['geometry']['viewport']['northeast']['lat'],
        'lng_north_east' => empty($details['result']['geometry']['viewport']) ? $details['result']['geometry']['location']['lng'] + 0.005 : $details['result']['geometry']['viewport']['northeast']['lng'],
        'lat_south_west' => empty($details['result']['geometry']['viewport']) ? $details['result']['geometry']['location']['lat'] - 0.005 : $details['result']['geometry']['viewport']['southwest']['lat'],
        'lng_south_west' => empty($details['result']['geometry']['viewport']) ? $details['result']['geometry']['location']['lng'] - 0.005 : $details['result']['geometry']['viewport']['southwest']['lng'],
      ],
      'address' => empty($details['result']['formatted_address']) ? '' : $details['result']['formatted_address'],
    ];
  }

}
