<?php
namespace Drupal\een_common\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class VerifyCodeForm extends AbstractForm
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
     * @return ExpressionOfInterestForm
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
        return 'een_verify_for_pwd_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [

            'tokenme' => [
                '#type'          => 'textfield',
                //'#value'       => $this->t('6 digit code'),
                '#placeholder'       => $this->t('6 digit code'),
                '#label_display' => 'before',
                '#required'      => false,
                '#attributes'    => [
                    'class' => [
                        'form-control entered-verification',
                    ],
                ],
            ],
            'actions' => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Sign In'),
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

        if(crypt($form_state->getValue('tokenme'), \Drupal::config('een_salesforce.settings')->get('hash_key')) != $this->session->get('password-reset')){
            $form_state->setErrorByName(
                'tokenme',
                [
                    'key'          => 'edit-token',
                    'text'         => t('Code does not match.'),
                    'general_text' => t('Code does not match.'),
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->session->set('password-reset-verified', true);
        $form_state->setRedirect('reset-password-new', array());
        return 1;
    }
}