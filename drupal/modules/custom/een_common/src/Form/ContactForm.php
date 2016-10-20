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
        $form = [
            'firstname'     => [
                '#type'           => 'textfield',
                '#title'          => t('First name'),
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
            'lastname'      => [
                '#type'           => 'textfield',
                '#title'          => t('Last name'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-lastname',
                    'text'         => t('This is required to contact us.'),
                    'general_text' => t('The last name is required to contact us.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'family-name',
                    ],
                ],
            ],
            'contact_email' => [
                '#type'           => 'email',
                '#title'          => t('Email'),
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
                '#title'          => t('Contact telephone number'),
                '#label_display'  => 'before',
                '#required'       => true,
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
        $form_state->disableRedirect();
    }
}