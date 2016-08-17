<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\elastic_search\Service\ElasticSearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class OpportunitiesForm extends FormBase
{
    /**
     * @var ElasticSearchService
     */
    private $service;

    /**
     * OpportunitiesForm constructor.
     *
     * @param ElasticSearchService $service
     */
    public function __construct(ElasticSearchService $service)
    {
        $this->service = $service;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return OpportunitiesForm
     */
    public static function create(ContainerInterface $container)
    {
        return new self(
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
        $types = [
            'BO' => t('A product or service to buy'),
            'BR' => t('A partner to sell my products, services'),
            'TR' => t('A manufacturer to produce my products'),
            'TO' => t('A technology, expertise'),
            'RD' => t('A partner to develop a new technology'),
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
                '#title'   => t('I am looking for'),
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
            '#method'           => Request::METHOD_GET,
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (strlen($form_state->getValue('search')) < 2) {
            $form_state->setErrorByName(
                'search',
                [
                    'key'  => 'edit-search',
                    'text' => t('Please enter at least 2 characters to perform a search.'),
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $form_state->disableRedirect();

        return null;
        // TODO Return json when POST is used and we are making nice and pretty url
//        $values = $form_state->getValues();
//        $form_state->setRedirect(
//            'opportunities.search',
//            [],
//            [
//                'query' => [
//                    'search'           => $values['search'],
//                    'opportunity_type' => $values['opportunity_type'],
//                ],
//            ]
//        );
    }
}