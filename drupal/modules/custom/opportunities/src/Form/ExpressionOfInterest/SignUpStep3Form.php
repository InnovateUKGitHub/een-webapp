<?php
namespace Drupal\opportunities\Form\ExpressionOfInterest;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Controller\OpportunityController;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SignUpStep3Form extends AbstractForm
{
    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * SignUpStep3Form constructor.
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
     * @return SignUpStep3Form
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
        return 'sign_up_step_2_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [
            'postcode'       => [
                '#type'          => 'textfield',
                '#title'         => t('Enter your postcode'),
                '#label_display' => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-postcode',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The postcode is required to complete your application.'),
                ],
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ]
                ],
            ],
            'addressone'       => [
                '#type'          => 'textfield',
                '#title'         => t('Address Line 1'),
                '#label_display' => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-addressone',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The address is required to complete your application.'),
                ],
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ]
                ],
            ],
            'addresstwo'       => [
                '#type'          => 'textfield',
                '#title'         => t('Address Line 2'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ]
                ],
            ],
            'city'       => [
                '#type'          => 'textfield',
                '#title'         => t('Town/City'),
                '#label_display' => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-city',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The city is required to complete your application.'),
                ],
                '#attributes'    => [
                    'class' => [
                        'form-control',
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
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $form_state->setRedirect(
            'opportunities.eoi.review',
            [
                'profileId' => $this->session->get('profileId'),
            ]
        );

        $this->session->set('postcode', $form_state->getValue('postcode'));
        $this->session->set('addressone', $form_state->getValue('addressone'));
        $this->session->set('addresstwo', $form_state->getValue('addresstwo'));
        $this->session->set('city', $form_state->getValue('city'));

        $this->session->set('reference_number', random_int(1111, 9999) . '-' . random_int(1111, 9999));
    }
}