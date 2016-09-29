<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/*
 * 
 * TEMP FORM
 * 
 */

class CompaniesHouseForm extends AbstractForm
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'companies_house';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [
            'company_name' => [
                '#type'                => 'textfield',
                '#title'               => t('Company Name'),
                '#label_display'       => 'before',
                '#attributes'          => [
                    'class' => [
                        'form-control ch_search',
                    ],
                    'placeholder' => [
                        'Your company\'s name',
                    ],
                    'id' => [
                        'ch_search'
                    ]
                ],
            ],
            
            'search'       => [
                '#type'          => 'html_tag',
                '#title'         => t('Search Companies House'),
                '#label_display' => 'before',
                '#executes_submit_callback' => false,
                '#value' => 'Search Companies House',
                '#tag' => 'button',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                    'id' => [
                        'ch-search-trigger',
                    ]
                ],
            ],
            
            
            'company_number'       => [
                '#type'          => 'textfield',
                '#title'         => t('Company number'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            
            'no_company_number'       => [
                '#type'          => 'checkbox',
                '#title'         => t('I do not have a Companies House number'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            
            'actions'     => [
                '#type'  => 'actions',
                '#attributes'    => [
                    'id' => [
                        'ch_search_pp',
                    ],
                ],
                
                
                'submit' => [
                    '#type'        => 'button',
                    '#value'       => $this->t('Search Companies House'),
                    '#button_type' => 'primary',
                    '#method' => 'append',
                    '#url' => '/opportunities-tempajax',
                    '#ajax'        => [
                        'callback' => 'Drupal\opportunities\Controller\OpportunitiesController::tempajax',
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
        $response = parent::generateAjaxError($form, $form_state, ['description', 'interest', 'more', 'email', 'phone']);

        if (!$form_state->getErrors()) {
            $response->addCommand(new OpenModalDialogCommand('Thank you', 'Your expression of interest has been recorded'));
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::checkRequireField($form_state, 'description', t('A short description of your organisation is required to complete your application.'));
        parent::checkRequireField($form_state, 'interest', t('Details of your interest in this opportunity are required to complete your application.'));
        parent::checkEmailAndPhoneField($form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // TODO Submit Form to api
        return false;
    }
}
