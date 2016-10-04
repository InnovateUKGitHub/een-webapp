<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Controller\OpportunityController;
use Drupal\user\PrivateTempStore;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SignUpStep1Form extends AbstractForm
{
    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * SignUpStep1Form constructor.
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
     * @return SignUpStep1Form
     */
    public static function create(ContainerInterface $container)
    {
        return new self($container->get('user.private_tempstore')->get(OpportunityController::SESSION));
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'sign_up_step_1_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $types = [
            'UK' => t('UK Newsletter'),
            'EE' => t('East of England'),
            'L' => t('London'),
            'M' => t('Midlands'),
            'NE' => t('North England'),
            'NI' => t('Northern Ireland'),
            'SE' => t('South East England'),
            'SW' => t('South West England'),
            'W' => t('Wales'),
        ];
        $radio = [
            'UK' => t('UK Newsletter'),
            'EE' => t('East of England'),
        ];

        $form = [
            'firstname'       => [
                '#type'          => 'textfield',
                '#title'         => t('Fisrt name'),
                '#label_display' => 'before',
                '#required' => true,
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ]
                ],
            ],
            'lastname'       => [
                '#type'          => 'textfield',
                '#title'         => t('Last name'),
                '#label_display' => 'before',
                '#required' => true,
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ]
                ],
            ],
            'email'       => [
                '#type'          => 'email',
                '#title'         => t('Email'),
                '#label_display' => 'before',
                '#required' => true,
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ]
                ],
            ],
            'phone'       => [
                '#type'          => 'textfield',
                '#title'         => t('Contact telephone number'),
                '#label_display' => 'before',
                '#required' => true,
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ]
                ],
            ],
            'newsletter' => [
                '#type'    => 'checkboxes',
                '#title'   => t('Please send me emails when there is a new:'),
                '#options' => $types,
            ],
            'radiobutton' => [
                '#type'    => 'radios',
                '#title'   => t('Please send me emails when there is a new:'),
                '#options' => $radio,
                '#attributes' => [
                    'class' => [
                        'radio-buttons',
                    ]
                ],
            ],
            'actions'     => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Continue'),
                    '#button_type' => 'primary',
                ],
            ],
            '#method'     => Request::METHOD_POST,
        ];
        $form_state->setCached(false);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $form_state->setRedirect(
            'opportunities.eoi.step2',
            [
                'profileId' => $this->session->get('profileId'),
            ]
        );

        $this->session->set('firstname', $form_state->getValue('firstname'));
        $this->session->set('lastname', $form_state->getValue('lastname'));
        $this->session->set('email-company', $form_state->getValue('email'));
        $this->session->set('phone-company', $form_state->getValue('phone'));
        $this->session->set('newsletter', $form_state->getValue('newsletter'));
        $this->session->set('radiobutton', $form_state->getValue('radiobutton'));
    }
}