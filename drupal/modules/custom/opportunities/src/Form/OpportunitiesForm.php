<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Service\OpportunitiesService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class OpportunitiesForm extends AbstractForm
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
        return 'opportunity_search_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $types = [
            'BO' => $this->t('to buy from'),
            'BR' => $this->t('to sell to'),
            'TR' => $this->t('that needs my tech/expertise'),
            'TO' => $this->t('with tech/expertise that I need'),
            'RDR' => $this->t('to collaborate with/co-develop with'),
        ];

        $regions = array();
        $regions['europe'] = 'Europe';
        $regions['anywhere'] = 'Anywhere in the world';

        $countries = array_merge($regions, $this->service->getCountryList());

        $form = [
           /* 'search'           => [
                '#type'       => 'textfield',
                '#title'      => $this->t('Contains keywords...'),
                '#attributes' => [
                    'ng-model'    => 'data.search',
                    'ng-change'   => 'queryKeyUp()',
                    'tabindex'    => '0',
                    'class'       => [
                        'form-control',
                    ],
                    'placeholder' => [
                        'E.g. medical component distribution',
                    ],
                ],
            ],*/

            'opportunity_type' => [
                '#type'       => 'checkboxes',
                '#title'      => $this->t('I’m looking for a partner...'),
                '#options'    => $types,
                '#attributes' => [
                    'tabindex' => '0',
                    'ng-click' => 'selectOppCheckbox($event)',
                ],
            ],

            'country'          => [
                '#type'       => 'checkboxes',
                '#title'      => $this->t('Country of origin'),
                '#options'    => $countries,
                '#attributes' => [
                    'tabindex' => '0',
                    'ng-click' => 'selectCountryCheckbox($event)',
                    'class'    => [
                        'accordion-container parent-country-regions',
                    ],
                ],
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
            '#attributes'      => [
                'tabindex'  => '0',
                'ng-submit' => "submit()",
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
