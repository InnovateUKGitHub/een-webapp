<?php
namespace Drupal\een_common\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\events\Service\EventsService;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\user\PrivateTempStore;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\Request;

abstract class EmailVerificationForm extends AbstractForm
{
    /** @var PrivateTempStore */
    protected $session;
    /** @var OpportunitiesService|EventsService */
    protected $service;
    /** @var string */
    protected $email;
    /** @var string */
    protected $token;
    /** @var string */
    protected $id;

    /**
     * OpportunitiesController constructor.
     *
     * @param OpportunitiesService|EventsService $service
     * @param PrivateTempStoreFactory            $tempStore
     */
    public function __construct($service, PrivateTempStoreFactory $tempStore)
    {
        $this->service = $service;
        $this->session = $tempStore->get('SESSION_ANONYMOUS');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'email_verification_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [
            'emailverification' => [
                '#type'          => 'email',
                '#title'         => t('Enter your email to'),
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
                            'verify-my-email',
                        ],
                    ],
                ]
            ],

            'token' => [
                '#type'          => 'textfield',
                //'#value'       => $this->t('6 digit code'),
                '#placeholder'       => $this->t('6 digit code'),
                '#title'         => '6 digit code',
                '#label_display' => 'before',
                '#required'      => false,
                '#attributes'    => [
                    'class' => [
                        'form-control disabled entered-verification',
                    ],
                    'disabled' => [
                        'disabled',
                    ],
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

        if (!parent::checkRegexField($form_state, self::EMAIL_REGEX, 'emailverification')) {
            $form_state->setErrorByName(
                'emailverification',
                [
                    'key'          => 'edit-emailverification',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The email is required to verify your identity.'),
                ]
            );
        }
        if($form_state->getValue('token') != $this->session->get('token')){
            $form_state->setErrorByName(
                'token',
                [
                    'key'          => 'edit-token',
                    'text'         => t('Token does not match.'),
                    'general_text' => t('Token does not match.'),
                ]
            );
        }
    }

    /**
     * @param FormStateInterface $form_state
     * @param string             $message
     */
    public function submit(FormStateInterface $form_state, $message)
    {

        $this->id = $form_state->getValue('id');
        $this->email = $form_state->getValue('emailverification');

        if(!$form_state->getValue('token')){
            $this->token = mt_rand(100000, 999999);
            $form_state->disableRedirect();
            drupal_set_message($message);
        } else {
            $this->token = $form_state->getValue('token');
        }

        $this->session->set('id', $this->id);
        $this->session->set('email', $this->email);
        $this->session->set('token', $this->token);

        $form_state->setRedirect('opportunities.details',
            [
                'profileId' => $this->id,
                'token' => $form_state->getValue('token')
            ]
        );

        if($form_state->getValue('token')){
            return 0;
        }
        return 1;
    }
}