<?php

namespace Drupal\opportunities\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\elastic_search\Service\ElasticSearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OpportunitiesForm extends FormBase
{
    private $results;

    /**
     * @var ElasticSearchService
     */
    protected $service;

    public function __construct(ElasticSearchService $service)
    {
        $this->service = $service;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            \Drupal::service('elastic_search.connection')
        );
    }

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
            $form_state->setErrorByName('search', t('Please enter at least 1 characters to perform a search.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $values = $form_state->getValues();

        $this->service->setUrl('opportunities')
            ->setSearchParams([
                'search' => $values['search'],
                'sort' => [
                    ['date' => 'desc']
                ],
                'source' => ['name', 'type', 'date', 'description', 'country', 'opportunity_type']
            ]);

        $results = $this->service->sendRequest();

        if (array_key_exists('error', $results)) {
            drupal_set_message($results['error'], 'error');
            return;
        }
        $this->results = $results;
        $form_state->setRebuild();
    }
}