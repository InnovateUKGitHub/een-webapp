<?php

namespace Drupal\update_preferences\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\een_common\Service\ContactService;


/**
 * Admin form to allow transferring of JSON to S3.
 */
class UpdatePreferencesForm extends AbstractForm {


  /**
   * @var ContactService
   */
  private $service;

  /**
   * @var PrivateTempStore
   */
  private $session;

  /**
   * SignUpStep1Form constructor.
   *
   * @param PrivateTempStore $session
   */
  public function __construct(
      PrivateTempStore $session,
      ContactService $service
  )
  {
    $this->session = $session;
    $this->service = $service;
  }

  /**
   * @param ContainerInterface $container
   *
   * @return SignUpStep1Form
   */
  public static function create(ContainerInterface $container)
  {
    return new self(
        $container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'),
        $container->get('contact.service')
    );
  }


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'update_preferences_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'update_preferences';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $query = \Drupal::entityQuery('node')
        ->condition('type', 'client_options');
    $nids = $query->execute();
    $node = node_load(end($nids));

    $subjects = [];
    foreach ($node->get('field_subjects_of_interest')->getValue() as $interest) {
      $value = explode('|', $interest['value']);
      $subjects[$value[1]] = $value[0];
    }
    $updatesWanted = [];
    foreach ($node->get('field_types_of_updates')->getValue() as $updates) {
      $value = explode('|', $updates['value']);
      $updatesWanted[$value[1]] = $value[0];
    }

    $types = [];
    if ($node->get('field_newsletter_otions')->getValue()) {
      foreach ($node->get('field_newsletter_otions')->getValue() as $type) {
        $value = explode('|', $type['value']);
        $types[$value[1]] = $value[0];
      }
    }


    $form = [
        'contact_id' => [
            '#type' => 'hidden',
            '#title' => t('<h3 tabindex="0" class="heading-medium">Contact Id:</h3>'),
        ],
        'email' => [
            '#type' => 'hidden',
            '#title' => t('<h3 tabindex="0" class="heading-medium">Contact Id:</h3>'),
        ],
        'newsletter' => [
            '#type' => 'checkboxes',
            '#title' => t('<h3 tabindex="0" class="heading-medium">I want to receive the latest newsletter...</h3>'),
            '#options' => $types,
            '#attributes'     => [
                'class'        => [
                    'js-update-checkboxes',
                ]
            ],
        ],
        'subjects' => [
            '#type' => 'checkboxes',
            '#required' => false,
            '#title' => t('<h3 tabindex="1" class="heading-medium">Subjects of interest</h3>'),
            '#options' => $subjects
        ],

        'update_type' => [
            '#type' => 'checkboxes',
            '#title' => t('<h3 tabindex="2" class="heading-medium">What types of updates do you want from us?</h3>'),
            '#options' => $updatesWanted
        ],

        'terms' => [
            '#type' => 'checkbox',
            '#title' => t('I have read and accept the <a href="/privacy-notice" target="_blank">privacy notice policy</a>'),
            '#required' => true
        ],
        'unsubscribe' => [
            '#type' => 'checkbox',
            '#title' => t('Or, please unsubscribe me from email newsletters'),
            '#required' => false,
            '#attributes'     => [
                'class'        => [
                    'js-unsubscribe-checkboxes',
                ]
            ],
        ],

        'actions' => [
            '#type' => 'actions',
            'submit' => [
                '#type' => 'submit',
                '#value' => $this->t('Save'),
                '#button_type' => 'primary',
            ],
        ],
        '#method' => Request::METHOD_POST,
    ];

    $form_state->setCached(false);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

      try {
          db_insert('update_preferences')
              ->fields(array('email' => $form_state->getValue('email'), 'date' => date('Y-m-d H:i:s')))
              ->execute();
      } catch(\Exception $e){

      }


      $this->service->updateContactPreferences($form_state->getValue('contact_id'), $form_state);

      drupal_set_message('Thank you, your preferences have been saved.');

  }

  private function purgeValues($values)
  {
    return array_filter($values, function($value) {
      return !empty($value);
    });
  }

}
