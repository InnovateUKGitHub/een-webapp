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
            'BO' => t('to buy from'),
            'BR' => t('to sell to'),
            'TR' => t('that needs my technology or expertise'),
            'TO' => t('with technology or expertise that I need'),
            'RD' => t('to collaborate with'),
        ];
        
        $typesAccessibility = [
            '' => t('Select &hellip;'),
            'BO' => t('to buy from'),
            'BR' => t('to sell to'),
            'TR' => t('that needs my technology or expertise'),
            'TO' => t('with technology or expertise that I need'),
            'RD' => t('to collaborate with'),
        ];
        
        
        $countries = $this->service->getCountryList();

        $countryChoices = [
            'anywhere' => t('anywhere in the world'),
            'europe' => t('in europe'),
            '' => t('specific countries')
        ];
        
        $countryChoicesAccessibility = [
            '' => t('Select &hellip;'),
            'anywhere' => t('anywhere in the world'),
            'europe' => t('in europe')
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
            
            'opportunity_type_hidden' => [
                '#type'    => 'select',
                '#title'   => t('Choose a partnership type &hellip;'),
                '#options' => $typesAccessibility,
                '#attributes' => [
                    'class' => [
                        'form-types sr-only form-control hidden-select-forms',
                    ],
                    'tabindex' => [
                        "0"
                    ],
                    'aria-label' => [
                        "Choose a partnership type"
                    ],
                ]
            ],
            
            'opportunity_type' => [
                '#type'    => 'radios',
                '#title'   => t('Choose a partnership type &hellip;'),
                '#options' => $types,
                '#attributes' => [
                    'aria-hidden' => [
                        "true"
                    ],
                    'class' => [
                        'form-types',
                    ],
                ]
            ],
            
            'country_choice_hidden' => [
                '#type'    => 'select',
                '#title'   => t('Where &hellip;'),
                '#options' => $countryChoicesAccessibility,
                '#attributes' => [
                    'tabindex' => [
                        "0"
                    ],
                    'class' => [
                        'form-countries sr-only form-control hidden-select-forms',
                    ],
                    'aria-label' => [
                        "Choose a location"
                    ],
                ]
            ],
            

            'country_choice' => [
                '#type'    => 'radios',
                '#title'   => t('Where &hellip;'),
                '#options' => $countryChoices,
                '#attributes' => [
                    'aria-hidden' => [
                        "true"
                    ],
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
