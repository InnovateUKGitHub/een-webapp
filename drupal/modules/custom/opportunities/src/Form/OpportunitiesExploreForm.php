<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Service\OpportunitiesService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class OpportunitiesExploreForm extends AbstractForm
{
    /**
     * @var OpportunitiesService
     */
    private $service;

    /**
     * OpportunitiesController constructor.
     *
     * @param OpportunitiesService $service
     */
    public function __construct(OpportunitiesService $service)
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
        return new self($container->get('opportunities.service'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'opportunity_search_explore_form';
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
        $countries = $this->service->getCountryList();
        
        $countryChoices = [
            'anywhere' => t('anywhere in the world'),
            'europe' => t('in europe'),
            '' => t('specific countries')
        ];

        $form = [
            'search'           => [
                '#type'       => 'hidden',
                '#title'      => t('Search an opportunity'),
                '#attributes' => [
                    'class' => [
                        'form-control',
                    ],
                    'placeholder' => [
                        'E.g. medical component distribution',
                    ],
                    'id' => [
                        'search'
                    ]
                ],
            ],
            'opportunity_type' => [
                '#type'    => 'radios',
                '#title'   => t('Choose a partnership type...'),
                '#options' => $types,
                '#attributes' => [
                    'ng-click' => 'selectOppCheckbox($event)',
                    'class' => [
                        'form-types',
                    ],
                ]
            ],
            
            
            'country_choice' => [
                '#type'    => 'radios',
                '#title'   => t('Where.. '),
                '#options' => $countryChoices,
                '#attributes' => [
                    'ng-click' => 'selectOppCheckbox($event)',
                    'class' => [
                        'form-countries',
                    ],
                ]
            ],
            
            'country'          => [
                '#type'       => 'select',
                '#title'      => t('Country of origin'),
                '#options'    => $countries,
                '#attributes' => [
                    //'ng-click' => 'selectCountryCheckbox($event)',
                    'class' => [
                        'chosen-select chosen-select-multiple form-countries-all',
                    ],
                    'data-placeholder' => 'Choose your countries',
                    'multiple' => true,
                    'tabindex' => 8,
                ],
            ],
            'actions'          => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('View results'),
                    '#button_type' => 'primary',
                ],
            ],
            
            '#method'          => Request::METHOD_POST,
            '#attributes'      => [
                'ng-submit' => "submit()",
                'class' => [
                    'explore-form',
                ],
            ],
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $values = $form_state->getValues();

        $search = $values['search'];
        $opportunity_type = $this->filterValues($values, 'opportunity_type');
        $country = $this->filterValues($values, 'country');

        $params = [];
        if (empty($search) === false) {
            $params['search'] = $values['search'];
        }
        if (empty($opportunity_type) === false) {
            $params['opportunity_type'] = $opportunity_type;
        }
        if (empty($country) === false) {
            $params['country'] = $country;
        }

        $form_state->setRedirect(
            'opportunities.search',
            [],
            [
                'query' => $params,
            ]
        );
    }
}
