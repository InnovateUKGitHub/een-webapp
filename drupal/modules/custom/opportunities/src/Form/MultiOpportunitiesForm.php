<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

class MultiOpportunitiesForm extends FormBase
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
        $types = [
            'BO' => t('buy a product or service'),
            'BR' => t('sell my product or service'),
            'TR' => t('find a manufacturer or licensee'),
            'TO' => t('find a specialist'),
            'RD' => t('develop tech / bid for funding'),
        ];

        $form = [
            'search'           => [
                '#type'       => 'textfield',
                '#title'      => t('Search an opportunity'),
                '#attributes' => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'opportunity_type' => [
                '#type'    => 'checkboxes',
                '#title'   => t('I want to...'),
                '#options' => $types,
            ],
            'actions'          => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Search'),
                    '#button_type' => 'primary',
                ],
            ],
            '#method'          => Request::METHOD_POST,
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $values = $form_state->getValues();
        $form_state->setRedirect(
            'opportunities.search.test',
            [],
            [
                'query' => [
                    'search'           => $values['search'],
                    'opportunity_type' => $values['opportunity_type'],
                ],
            ]
        );
    }
}