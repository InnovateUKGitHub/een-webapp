<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

class ExpressionOfInterestForm extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'opportunity_search_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [
            'description' => [
                '#type'                => 'textarea',
                '#title'               => t('Short description of your organisation, activities, products and services'),
                '#field_prefix'        => t('Why do EEN need this?'),
                '#description'         => t('Lorem ipsum'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#attributes'          => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'interest'    => [
                '#type'                => 'textarea',
                '#title'               => t('What interests you about this opportunity and what do you expect of that organisation?'),
                '#field_prefix'        => t('Why do EEN need this?'),
                '#description'         => t('Lorem ipsum'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#attributes'          => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'more'        => [
                '#type'                => 'textarea',
                '#title'               => t('Is there anything further you would like to know about this opportunity?'),
                '#field_prefix'        => t('Why do EEN need this?'),
                '#description'         => t('Lorem ipsum'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#attributes'          => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'email'       => [
                '#type'                => 'textfield',
                '#title'               => t('Email'),
                '#description'         => t('You will be asked to create your EEN account if you do not already have one. If you do not have an email address, please enter your phone number instead.'),
                '#description_display' => 'after',
                '#label_display'       => 'before',
                '#attributes'          => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'actions'     => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Express your interest in this opportunity'),
                    '#button_type' => 'primary',
                    '#ajax'        => [
                        'callback' => [$this, 'submitHandler'],
                    ],
                ],
            ],
            '#method'     => Request::METHOD_POST,
        ];
        $form_state->setCached(false);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitHandler(array &$form, FormStateInterface $form_state)
    {
        $response = new AjaxResponse();
        $response->addCommand(new HtmlCommand('.js-form-item .error-message', ''));
        $response->addCommand(new InvokeCommand('.js-form-item.error', 'removeClass', ['error']));
        if ($form_state->getErrors()) {
            foreach ($form_state->getErrors() as $error) {
                $response->addCommand(
                    new HtmlCommand('.js-form-item-' . $error['element'] . ' .error-message', $error['text'])
                );
                $response->addCommand(
                    new InvokeCommand('.js-form-item-' . $error['element'], 'addClass', ['error'])
                );
            }
        } else {
            $response->addCommand(new OpenModalDialogCommand('Thank you', 'Your expression of interest has been recorded'));
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (strlen($form_state->getValue('description')) < 1) {
            $form_state->setErrorByName(
                'description',
                [
                    'element' => 'description',
                    'key'     => 'edit-description',
                    'text'    => t('The description is required.'),
                ]
            );
        }
        if (strlen($form_state->getValue('interest')) < 1) {
            $form_state->setErrorByName(
                'interest',
                [
                    'element' => 'interest',
                    'key'     => 'edit-interest',
                    'text'    => t('The interest is required.'),
                ]
            );
        }
        if (strlen($form_state->getValue('more')) < 1) {
            $form_state->setErrorByName(
                'more',
                [
                    'element' => 'more',
                    'key'     => 'edit-more',
                    'text'    => t('The more is required.'),
                ]
            );
        }
        if (strlen($form_state->getValue('email')) < 1) {
            $form_state->setErrorByName(
                'email',
                [
                    'element' => 'email',
                    'key'     => 'edit-email',
                    'text'    => t('The email/phone number is required.'),
                ]
            );
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // TODO Submit Form to api
    }
}