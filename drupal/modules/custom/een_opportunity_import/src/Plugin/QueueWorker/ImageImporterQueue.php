<?php

namespace Drupal\een_opportunity_import\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use XMLReader;
use DOMDocument;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;


/**
 * Processes an individual POD for images and copies them.
 *
 * @QueueWorker(
 *   id = "image_importer_queue",
 *   title = @Translation("POD Image Import Queue"),
 *   cron = {"time" = 90}
 * )
 */
class ImageImporterQueue extends QueueWorkerBase {
  
  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Get the start date
    $startdate = $data['day'];
    $enddate = $data['end_day'];
    $url = 'http://een.ec.europa.eu/tools/services/podv6/QueryService.svc/GetProfiles?u=UK00774&p=c9a0974cb8ba283ea6e31ca3683f9ebf&im=true&sa=' . $startdate . '&sb=' . $enddate;
    $opts = array(
        'http' => array(
            'user_agent' => 'PHP libxml agent',
        )
    );

    $context = stream_context_create($opts);
    libxml_set_streams_context($context);
    $reader = new \XMLReader;
    $reader->open($url, NULL, NULL);

    $doc = new DOMDocument;

    while ($reader->read() && $reader->name !== 'profile');

    while ($reader->name === 'profile')
    {
      $xmlNode = simplexml_import_dom($doc->importNode($reader->expand(), true));

        $profileId = (string)$xmlNode->reference->external[0];

        /// get node id;
        $query = \Drupal::entityQuery('node');

        $fids =  $query
            ->condition('type', 'partnering_opportunity')
            ->condition('field_opportunity_id', $profileId)
            ->execute();

        $arrayValues = array_values($fids);
        $id = array_shift($arrayValues);

        if(!$id){
            break;
        }

        $node = Node::load($id);
        //reset
        $node->field_opportunity_images = [];
        $node->save();


        if($xmlNode->files->profileFile){

          foreach($xmlNode->files->profileFile as $value) {

              $base64_string = $value->data;
              $filePath = "s3://podimages/".$value->name;

              file_put_contents($filePath, base64_decode($base64_string));

              $uri = 's3://podimages/'.$value->name;

              try {
                  // check first if the file exists for the uri
                  $files = \Drupal::entityTypeManager()
                      ->getStorage('file')
                      ->loadByProperties(['uri' => $uri]);
                  $file = reset($files);

                  // if not create a file
                  if (!$file) {
                      $file = File::create([
                          'uri' => $uri,
                      ]);
                      $file->save();
                  }

                  $node->field_opportunity_images[] = [
                      'target_id' => $file->id(),
                      'alt' => 'alt',
                      'title' => 'Title',
                  ];

                  $node->save();
              } catch (\Exception $exception){

              }
          }


            \Drupal::logger('pod_image_log')->info('%title %data',
                array(
                    '%title' => $id. "Images added for ". $profileId,
                    '%data' => json_encode($value)
                )
            );
        }


      $reader->next('profile');
    }
  }
  
}
