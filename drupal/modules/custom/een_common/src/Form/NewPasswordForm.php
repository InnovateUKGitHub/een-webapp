<?php
namespace Drupal\een_common\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\een_common\Service\ContactService;

class NewPasswordForm extends AbstractForm
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
     * @return ExpressionOfInterestForm
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
        return 'een_password_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [

            'password'  => [
                '#type'            => 'password',
                '#title'           => t('Password'),
                '#label_display'   => 'before',
                '#required'        => true,
                '#required_error' => [
                    'key'          => 'edit-password',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The password is required to complete your application.'),
                ],
                '#attributes'     => [
                    'size'        => [
                        null,
                    ]
                ],
            ],
            'actions' => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Update Password'),
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
        $this->session->set('password', $this->service->hashPassword($form_state->getValue('password')));
        drupal_set_message('Your password has been updated. Please log in below');
        $user = $this->service->createLead($this->session->get('password-reset-email'));

        if($user) {
            $this->service->updatePassword(array('id' => $user['Id'], 'password' => $this->service->hashPassword($form_state->getValue('password'))));

            $this->session->set('password-reset-verified', null);
            $this->session->set('password-reset-email', null);
            $this->session->set('password-reset', null);
            $form_state->setRedirect('login', array());
        } else {
            drupal_set_message('Sorry, your password could not be updated.');
        }

        return 1;
    }
}