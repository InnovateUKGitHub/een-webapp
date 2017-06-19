<?php
namespace Drupal\een_common\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Symfony\Component\HttpFoundation\Request;

class SignInForm extends AbstractForm
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'een_login_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [
            'email'     => [
                '#type'           => 'email',
                '#title'          => t('Email'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-email',
                    'text'         => t('This is required.'),
                    'general_text' => t('Required'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                ],

            ],
            'password'      => [
                '#type'           => 'password',
                '#title'          => t('Password'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-password',
                    'text'         => t('This is required.'),
                    'general_text' => t('Required'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ]
                ],
            ],
            'actions' => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Sign In'),
                    '#button_type' => 'primary',
                ],
            ],
            '#method' => Request::METHOD_GET,
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