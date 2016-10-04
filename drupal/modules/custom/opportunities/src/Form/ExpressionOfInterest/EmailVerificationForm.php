<?php
namespace Drupal\opportunities\Form\ExpressionOfInterest;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Controller\OpportunityController;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\user\PrivateTempStore;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class EmailVerificationForm extends AbstractForm
{
    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * @var OpportunitiesService
     */
    private $service;

    /**
     * OpportunitiesController constructor.
     *
     * @param OpportunitiesService    $service
     * @param PrivateTempStoreFactory $tempStore
     */
    public function __construct(OpportunitiesService $service, PrivateTempStoreFactory $tempStore)
    {
        $this->service = $service;
        $this->session = $tempStore->get(OpportunityController::SESSION);
    }

    /**
     * @param ContainerInterface $container
     *
     * @return EmailVerificationForm
     */
    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('opportunities.service'),
            $container->get('user.private_tempstore')
        );
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
                '#title'         => t('Email'),
                '#label_display' => 'before',
                '#required'      => true,
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'profile-id'         => [
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
        parent::checkRequireField($form_state, 'email-verification', t('An email is necessary to verify your identity.'));
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $form_state->disableRedirect();
        drupal_set_message('Thank you, please check your email to verify your identity.');

        $email = $form_state->getValue('email-verification');
        $token = bin2hex(random_bytes(50));
        $profileId = $form_state->getValue('profile-id');

        $this->session->set('email', $email);
        $this->session->set('token', $token);
        $this->session->set('profileId', $profileId);

        $this->service->verifyEmail($email, $token, $profileId);
    }
}