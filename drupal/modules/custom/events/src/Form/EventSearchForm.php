<?php
namespace Drupal\events\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class EventSearchForm extends AbstractForm
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'event_search_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [
            'search' => [
                '#type'       => 'textfield',
                '#title'      => $this->t('Search events'),
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

            'date_type' => [
                '#type'       => 'checkboxes',
                '#title'      => $this->t('Event date'),
                '#options'    => [
                    'any'  => $this->t('Any date'),
                    'pick' => $this->t('Choose dates between...'),
                ],
                '#attributes' => [
                    'tabindex'    => '0',
                    'ng-click'    => 'selectDateTypeCheckbox($event)',
                    'placeholder' => [
                        'Start',
                    ],
                ],
            ],

            'date' => [
                '#type'     => 'fieldset',
                'date_from' => [
                    '#type'       => 'textfield',
                    '#attributes' => [
                        'ng-model'    => 'data.date_from',
                        'ng-change'   => 'dateChange()',
                        'tabindex'    => '0',
                        'placeholder' => [
                            'Start',
                        ],
                        'class'       => [
                            'form-control',
                        ],
                    ],
                ],

                'date_to' => [
                    '#type'       => 'textfield',
                    '#attributes' => [
                        'ng-model'    => 'data.date_to',
                        'ng-change'   => 'dateChange()',
                        'tabindex'    => '0',
                        'placeholder' => [
                            'End',
                        ],
                        'class'       => [
                            'form-control',
                        ],
                    ],
                ],
            ],

            'country'     => [
                '#type'       => 'checkboxes',
                '#title'      => $this->t('Events by country'),
                '#options'    => [
                    'anywhere' => $this->t('Anywhere in the world'),
                    'europe'   => $this->t('Anywhere in Europe'),
                    'uk'       => $this->t('UK events only'),
                ],
                '#attributes' => [
                    'tabindex' => '0',
                    'ng-click' => 'selectCountryCheckbox($event)',
                    'class'    => [
                        'parent-country-regions',
                    ],
                ],
            ],
            'actions'     => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Search'),
                    '#button_type' => 'primary',
                ],
            ],
            '#method'     => Request::METHOD_POST,
            '#attributes' => [
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
        $country = $this->filterValues($values, 'country');
        $dateType = $this->filterValues($values, 'date_type');
        $dateFrom = $this->filterValues($values, 'date_from');
        $dateTo = $this->filterValues($values, 'date_to');

        $params = [];
        if (empty($search) === false) {
            $params['search'] = $values['search'];
        }
        if (empty($country) === false) {
            $params['country'] = $country;
        }
        if (empty($dateType) === false) {
            $params['date_type'] = $dateType;
        }
        if (empty($dateFrom) === false) {
            $params['date_from'] = $dateFrom;
        }
        if (empty($dateTo) === false) {
            $params['date_to'] = $dateTo;
        }

        $form_state->setRedirect(
            'events.search',
            [],
            [
                'query' => $params,
            ]
        );
    }
}