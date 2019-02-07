<?php

namespace Drupal\merlin\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Aws\S3\S3Client;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Provides a Merlin Rest Resource
 *
 * @RestResource(
 *   id = "merlin_rest_api",
 *   label = @Translation("Merlin Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/merlin"
 *   }
 * )
 */
class MerlinResource extends ResourceBase {


    /**
     * Responds to entity GET requests.
     *
     * @return \Drupal\rest\ResourceResponse
     */
    public function get() {

        header("Content-type: text/xml");

        $bucket = 'een-api-mirror';
        $key = 'data.xml';

        $client = new S3Client([
          'version'     => 'latest',
          'region'      => 'eu-west-2',
        ]);

        try {
          $object = $client->getObject([
            'Bucket' => $bucket,
            'Key' => $key,
          ]);

          echo $object['Body']; die;

        } catch (\Exception $e) {
          return false;
        }

        $build = [
            '#cache' => [
                'max-age' => 0,
            ],
        ];

        $content = '';

        return (new ResourceResponse($content))->addCacheableDependency($build);
    }


}