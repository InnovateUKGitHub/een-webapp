<?php
/**
 * @file
 * Contains \Drupal\simple_mail\Plugin\QueueWorker\SimpleMailSendQueuedMail.
 */

namespace Drupal\een_common\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * @QueueWorker(
 *   id = "pod_alert_queue",
 *   title = @Translation("Swift Mail Send Queued Email"),
 *   cron = {"time" = 60}
 * )
 */
class SwiftMailSendQueuedMail extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($message) {

    $smtpHost       = \Drupal::config('swiftmailer.transport')->get('smtp_host');
    $smtpPort       = \Drupal::config('swiftmailer.transport')->get('smtp_port');
    $smtpUser       = \Drupal::config('swiftmailer.transport')->get('smtp_username');
    $smtpPassword   = \Drupal::config('swiftmailer.transport')->get('smtp_password');
    $smtpEncryption = \Drupal::config('swiftmailer.transport')->get('smtp_encryption');


    $transport = new \Swift_SmtpTransport($smtpHost, $smtpPort, $smtpEncryption);
    $transport->setPassword($smtpPassword);
    $transport->setUsername($smtpUser);

    $mailer = new \Swift_Mailer($transport);

    $message = (new \Swift_Message($message['subject']))
        ->setFrom($message['from'])
        ->setTo($message['to'])
        ->setBody($message['body'], 'text/html')
    ;

    // Send the message
    $mailer->send($message);
  }

}
