<?php
namespace Drupal\elastic_search\Form;

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
            'email-verification' => [
                '#type'          => 'email',
                '#title'         => t('First, please enter your email'),
                '#label_display' => 'before',
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
                    '#value'       => $this->t('Verify my email'),
                    '#button_type' => 'primary',
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
        if (!parent::checkRegexField($form_state, self::EMAIL_REGEX, 'email-verification')) {
            $form_state->setErrorByName(
                'email-verification',
                [
                    'key'          => 'edit-email-verification',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The email is required to verify your identity.'),
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
        $form_state->disableRedirect();
        drupal_set_message($message);

        $this->id = $form_state->getValue('id');
        $this->email = $form_state->getValue('email-verification');
        $this->token = bin2hex(random_bytes(50));

        $this->session->set('id', $this->id);
        $this->session->set('email', $this->email);
        $this->session->set('token', $this->token);
    }
}