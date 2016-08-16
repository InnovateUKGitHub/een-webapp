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
        $form = [
            'search'  => [
                '#type'     => 'textfield',
                '#title'    => t('Search an opportunity'),
                '#required' => true,
            ],
            'submit'  => [
                '#type'        => 'submit',
                '#value'       => $this->t('Search'),
                '#button_type' => 'primary',
            ],
            'actions' => [
                '#type'   => 'actions',
                '#method' => Request::METHOD_GET,
            ],
        ];

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
        $form_state->setRedirect(
            'opportunities.search',
            [],
            [
                'query' => ['search' => $values['search']],
            ]
        );
    }
}