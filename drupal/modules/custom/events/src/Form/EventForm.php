<?php
namespace Drupal\events\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class EventForm extends AbstractForm
{
    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * ExpressionOfInterestForm constructor.
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
     * @return EventForm
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
        return 'events_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [
            'dietary' => [
                '#type'                => 'textarea',
                '#title'               => $this->t('Please tell us about your food specificity if you have some'),
                '#description'         => $this->t('This is your allergies, preferences, etc...'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#required'            => false,
                '#attributes'          => [
                    'class'       => [
                        'form-control',
                    ],
                    'placeholder' => [
                        '',
                    ],
                ],
            ],
            'email'   => [
                '#type'          => 'textfield',
                '#title'         => $this->t('Your Email'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'actions' => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Register for the event'),
                    '#button_type' => 'primary',
                ],
            ],
            '#method' => Request::METHOD_POST,
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
        if ($this->session->get('type') === 'Client') {
            $form_state->setRedirect(
                'sign-up.review',
                [
                    'id'   => $this->session->get('id'),
                    'type' => 'events',
                ]
            );
        } else {
            $form_state->setRedirect(
                'sign-up.step1',
                [
                    'id'   => $this->session->get('id'),
                    'type' => 'events',
                ]
            );
        }

        $this->session->set('email-verification', true);
        $this->session->set('type', 'events');
        $this->session->set('dietary', $form_state->getValue('dietary'));
    }
}