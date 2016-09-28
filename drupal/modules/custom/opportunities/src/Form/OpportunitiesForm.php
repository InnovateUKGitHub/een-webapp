<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Service\OpportunitiesService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class OpportunitiesForm extends FormBase
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
            'BO' => t('buy a product or service'),
            'BR' => t('sell my product or service'),
            'TR' => t('find a manufacturer or licensee'),
            'TO' => t('find a specialist'),
            'RD' => t('develop tech / bid for funding'),
        ];
        $countries = $this->service->getCountryList();

        $form = [
            'search'           => [
                '#type'       => 'textfield',
                '#title'      => t('Search an opportunity'),
                '#attributes' => [
                    'ng-model' => 'query',
                    'ng-change' => 'queryKeyUp()',
                    'class' => [
                        'form-control',
                    ],
                    'placeholder' => [
                        'E.g. medical component distribution',
                    ],
                ],
            ],
            'opportunity_type' => [
                '#type'    => 'checkboxes',
                '#title'   => t('I want to...'),
                '#options' => $types,
                '#attributes' => [
                    'ng-click' => 'selectOppCheckbox($event)'
                ]
            ],
            'country'          => [
                '#type'       => 'checkboxes',
                '#title'      => t('Country of origin'),
                '#options'    => $countries,
                '#attributes' => [
                    'ng-click' => 'selectCountryCheckbox($event)',
                    'class' => [
                        'accordion-container',
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
        // TODO Return json when POST is used and we are making nice and pretty url
        // $form_state->disableRedirect();
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

    /**
     *
     * @param array  $values
     * @param string $name
     *
     * @return array
     */
    private function filterValues($values, $name)
    {
        if (empty($values[$name]) === false) {
            return array_filter($values[$name], function($value) {
                if ($value !== '0') {
                    return $value;
                }

                return false;
            });
        }

        return [];
    }
}
