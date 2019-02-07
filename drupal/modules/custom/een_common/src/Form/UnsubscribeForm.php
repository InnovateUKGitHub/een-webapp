<?php
namespace Drupal\een_common\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\een_common\Service\ContactService;
use Symfony\Component\HttpFoundation\JsonResponse;



class UnsubscribeForm extends AbstractForm
{


    /**
     * @var ContactService
     */
    private $service;

    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * ExpressionOfInterestForm constructor.
     *
     * @param PrivateTempStore $session
     */
    public function __construct(
        PrivateTempStore $session,
        ContactService $service
    )
    {
        $this->session = $session;
        $this->service = $service;
    }


    /**
     * @param ContainerInterface $container
     *
     */
    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'),
            $container->get('contact.service')
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'unsubscribe_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [
            'emailverification' => [
                '#type'          => 'email',
                '#title'         => t('Enter your email access your preferences'),
                '#label_display' => 'before',
                '#placeholder'       => $this->t('Enter email address'),
                '#required'      => true,
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'id'                 => [
                '#type' => 'hidden',
            ],
            'actions'            => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Submit'),
                    '#button_type' => 'primary',
                    '#attributes'    => [
                        'class' => [
                            'verify-my-email-unsubscribe',
                        ],
                    ],
                ]
            ],

            'token' => [
                '#type'          => 'textfield',
                '#placeholder'       => $this->t('6 digit code'),
                '#title'         => '6 digit code',
                '#label_display' => 'before',
                '#required'      => false,
                '#attributes'    => [
                    'class' => [
                        'form-control entered-verification',
                    ]
                ],
            ],
            '#method'            => Request::METHOD_POST,
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

        $email = $_POST['emailverification'];
        $contactId = $this->service->getContactId($email);

        if(!$contactId || !$email){
            return;
        } else {
            $response = $this->service->verifyUnsubscribeEmail($email, $contactId);
            $this->session->set('isLoggedIn', false);
            $this->session->set('email', $email);
            $this->session->set('token', $response['token']);
            $this->session->set('link', $response['link']);
        }
    }
}