<?php
namespace Drupal\een_common\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Symfony\Component\HttpFoundation\Request;

class ContactForm extends AbstractForm
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'een_contact_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {


        $types = [
            'Innovation support' => 'Innovation support',
            'Moving into new markets' => 'Moving into new markets',
            'Finding partners' => 'Finding partners',
            'Using our networks' => 'Using our networks',
            'Accessing finance' => 'Accessing finance',
            'Other' => 'Other'
        ];

        $form = [
            'name'     => [
                '#type'           => 'textfield',
                '#title'          => t('Name'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-firstname',
                    'text'         => t('This is required to contact us.'),
                    'general_text' => t('The first name is required to contact us.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'given-name',
                    ],
                ],

            ],
            'contact_email' => [
                '#type'           => 'email',
                '#title'          => t('Email Address'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-contact-email',
                    'text'         => t('This is required to contact us.'),
                    'general_text' => t('The email is required to contact us.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'email',
                    ],
                ],
            ],
            'contact_phone' => [
                '#type'           => 'number',
                '#title'          => t('Phone'),
                '#label_display'  => 'before',
                '#required'       => false,
                '#required_error' => [
                    'key'          => 'edit-contact-phone',
                    'text'         => t('This is required to contact us.'),
                    'general_text' => t('The telephone number is required to contact us.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'tel',
                    ],
                ],
            ],
            'company_name' => [
                '#type'           => 'textfield',
                '#title'          => t('Company name'),
                '#label_display'  => 'before',
                '#required'       => false,
                '#required_error' => [
                    'key'          => 'edit-contact-company',
                    'text'         => t('This is required to contact us.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                ],
            ],
            'postcode' => [
                '#type'           => 'textfield',
                '#title'          => t('Postcode'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-contact-postcode',
                    'text'         => t('This is required to contact us.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                ],
            ],
            'enquiry_subject' => [
                '#type'    => 'select',
                '#title'   => t('My enquiry is about'),
                '#options' => $types,
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                ],
            ],
            'message'       => [
                '#type'           => 'textarea',
                '#title'          => t('Your Message'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-message',
                    'text'         => t('This is required to contact us.'),
                    'general_text' => t('The email is required to contact us.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'email',
                    ],
                ],
            ],

            'actions' => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Send'),
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
        $emailValues = [];
        $emailValues['name'] = $form_state->getValue('name');
        $emailValues['contact_email'] = $form_state->getValue('contact_email');
        $emailValues['contact_phone'] = $form_state->getValue('contact_phone');
        $emailValues['company_name'] = $form_state->getValue('company_name');
        $emailValues['postcode'] = $form_state->getValue('postcode');
        $emailValues['enquiry_subject'] = $form_state->getValue('enquiry_subject');
        $emailValues['message'] = $form_state->getValue('message');

        // Call the new 'Gov Notify' service instead of the old call to een-service which called 'Gov Delivery'
        $api_key = \Drupal::config('opportunities.settings')->get('api_key');
        $notifyClient = new \Alphagov\Notifications\Client([
            'apiKey' => $api_key,
            'httpClient' => new \Http\Adapter\Guzzle6\Client
        ]);

        $email_template_key = 'f3fdc912-9756-4870-b832-ea86945b000f';

        try {
            // Call the new 'Gov Notify' service to send verification email
            $response = $notifyClient->sendEmail('contact@enterprise-europe.co.uk', $email_template_key, $emailValues);
            drupal_set_message('Thank you, your message has been sent.');

        } catch (NotifyException $e) {
            drupal_set_message('There was a problem while sending the email, please try later.', 'error');
        }

        return $this->redirect(
            'een.contact',
            array()
        );

    }
}