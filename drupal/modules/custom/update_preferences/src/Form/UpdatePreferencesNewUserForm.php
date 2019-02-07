<?php

namespace Drupal\update_preferences\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\een_common\Service\ContactService;






class UpdatePreferencesNewUserForm extends \Drupal\update_preferences\Form\UpdatePreferencesForm
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
     * @param ContainerInterface $container
     *
     * @return SignUpStep1Form
     */
    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'),
            $container->get('contact.service')
        );
    }

    /**
     * SignUpStep1Form constructor.
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


    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $form = parent::buildForm($form, $form_state);
        $form['tokenme'] = [
            '#type'          => 'textfield',
            '#title'    => '<h3 tabindex="3" class="heading-medium">Please enter the 6 digit code which has been emailed to you</h3>',
            '#placeholder'       => $this->t('6 digit code'),
            '#label_display' => 'before',
            '#required'      => true,
            '#attributes'    => [
                'class' => [
                    'form-control',
                ]
            ],
        ];
        $form['userType'] = [
            '#type'          => 'hidden',
            '#default_value' => 'new',
        ];
        return $form;
    }


    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {

        if($form_state->getValue('tokenme') != $this->session->get('temptoken')){
            $form_state->setErrorByName(
                'tokenme',
                [
                    'key'          => 'edit-tokenme',
                    'text'         => t('Token does not match, please check your email and try again.'),
                    'general_text' => t('Token does not match, please check your email and try again.'),
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

        if($form_state->getValue('email')){

            $user = $this->service->createLead($form_state->getValue('email'));

            if(isset($user["Id"])){
                $this->service->updateContactPreferences($user["Id"], $form_state);
            }

            $form_state->setRedirect(
                'mm.manage-your-preferences',
                [],
                [
                    'query' => [
                        'email' => $form_state->getValue('email'),
                        't' => sha1($user['Email1__c'].$user['Id'])
                    ],
                ]
            );
            drupal_set_message('Thank you, your preferences have been saved.');
        }
    }
}

