<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Service\OpportunitiesService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SuperSearchForm extends AbstractForm
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
        return 'opportunity_super_search_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $form = [
            'search'           => [
                '#type'       => 'textfield',
                '#title'      => $this->t('Search by keyword <span class="suggestion-labels">e.g.  battery, engineering, biofuels</span>'),
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
            ],

            'actions'          => [
                '#attributes' => [
                    'class'       => [
                        'ss-submit'
                    ]
                ],
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
