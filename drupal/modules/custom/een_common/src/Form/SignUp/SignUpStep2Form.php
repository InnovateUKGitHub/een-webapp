<?php
namespace Drupal\een_common\Form\SignUp;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SignUpStep2Form extends AbstractForm
{
    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * SignUpStep2Form constructor.
     *
     * @param PrivateTempStore $session
     */
    public function __construct(PrivateTempStore $session)
    {
        $this->session = $session;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return SignUpStep2Form
     */
    public static function create(ContainerInterface $container)
    {
        return new self($container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'sign_up_step_2_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [
            'company_name'   => [
                '#type'          => 'textfield',
                '#title'         => t('Company Name'),
                '#label_display' => 'before',
                '#required'       => true,
                '#attributes'    => [
                    'class'       => [
                        'form-control ch_search',
                    ],
                    'placeholder' => [
                        'Your company\'s name',
                    ],
                    'id'          => [
                        'ch_search',
                    ],
                ],
            ],
            'search'         => [
                '#type'                     => 'html_tag',
                '#title'                    => t('Search Companies House'),
                '#label_display'            => 'before',
                '#executes_submit_callback' => false,
                '#value'                    => 'Search Companies House',
                '#tag'                      => 'button',
                '#attributes'               => [
                    'class' => [
                        'form-control',
                    ],
                    'id'    => [
                        'ch-search-trigger',
                    ],
                ],
            ],

            'no_company_number' => [
                '#type'  => 'checkbox',
                '#title' => t('I do not have a company number'),
                '#attributes'               => [
                    'class' => [
                        'no_company_number',
                    ],
                ],
            ],

            'company_number' => [
                '#type'          => 'textfield',
                '#title'         => t('Company number'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'website'        => [
                '#type'          => 'textfield',
                '#title'         => t('Website'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'company_phone'  => [
                '#type'          => 'textfield',
                '#title'         => t('Company switchboard phone number'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'actions'        => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Continue'),
                    '#button_type' => 'primary',
                ],
            ],
            '#method'        => Request::METHOD_POST,
        ];
        $form_state->setCached(false);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $form_state->setRedirect(
            'sign-up.step3',
            [
                'id'   => $this->session->get('id'),
                'type' => $this->session->get('type'),
            ]
        );

        $this->session->set('step2', true);
        $this->session->set('company_name', $form_state->getValue('company_name'));
        $this->session->set('company_number', $form_state->getValue('company_number'));
        $this->session->set('no_company_number', $form_state->getValue('no_company_number'));
        $this->session->set('website', $form_state->getValue('website'));
        $this->session->set('company_phone', $form_state->getValue('company_phone'));
    }
}