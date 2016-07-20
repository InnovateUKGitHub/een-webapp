<?php

namespace Drupal\opportunity_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunity_search\Service\ElasticSearchService;

class OpportunityForm extends FormBase
{
    private $results;

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
            'search' => [
                '#type'     => 'textfield',
                '#title'    => t('Search:'),
                '#required' => true,
            ],
            'actions' => [
                '#type' => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Search'),
                    '#button_type' => 'primary',
                ]
            ],
        ];
        if ($form_state->getValue('search')) {
            $form['results'] = $this->results;
        }
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (strlen($form_state->getValue('search')) < 1) {
            $form_state->setErrorByName('search', $this->t('Please enter at least 1 characters to perform a search.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $values = $form_state->getValues();

        $service = new ElasticSearchService();

        $service->setUrl('opportunity')
            ->setParams([
                'search' => $values['search'],
                'sort' => [
                    ['date' => 'desc']
                ],
                'source' => ['name', 'type', 'date', 'description', 'country', 'opportunity_type']
            ]);

        $this->results = $service->sendRequest();
        $form_state->setRebuild();
    }
}