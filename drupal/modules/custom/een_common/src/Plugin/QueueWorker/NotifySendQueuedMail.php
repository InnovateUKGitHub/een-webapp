<?php
/**
 * @file
 * Contains \Drupal\simple_mail\Plugin\QueueWorker\SimpleMailSendQueuedMail.
 */

namespace Drupal\een_common\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * @QueueWorker(
 *   id = "pod_alert_notify_queue",
 *   title = @Translation("POD ALERT Notify Send Queued Email"),
 *   cron = {"time" = 60}
 * )
 */
class NotifySendQueuedMail extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($message)
  {
    $api_key = \Drupal::config('opportunities.settings')->get('api_key');
    $notifyClient = new \Alphagov\Notifications\Client([
        'apiKey' => $api_key,
        'httpClient' => new \Http\Adapter\Guzzle6\Client
    ]);

    $email_template_key = '229411fd-50ea-4a66-84c4-d495a8238144';

    $parameters = [];
    $parameters['opportunities'] = $message['body'];
    $parameters['unsubscribe_link'] = 'https://www.enterprise-europe.co.uk/manage-your-preferences?email='.$message['to'].'&t='.$message['contactid'];
    if($_SERVER['APPLICATION_ENV'] == 'production_een_aws'){
      try {
        $response = $notifyClient->sendEmail($message['to'], $email_template_key, $parameters);
      } catch (NotifyException $e) {

      }
    }
  }
}
