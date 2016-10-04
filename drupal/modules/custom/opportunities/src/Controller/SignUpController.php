<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\opportunities\Form\ExpressionOfInterest\SignUpStep1Form;
use Drupal\opportunities\Form\ExpressionOfInterest\SignUpStep2Form;
use Drupal\opportunities\Form\ExpressionOfInterest\SignUpStep3Form;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SignUpController extends ControllerBase
{
    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var OpportunitiesService
     */
    private $service;

    /**
     * SignUpController constructor.
     *
     * @param OpportunitiesService    $service
     * @param PrivateTempStore        $session
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        OpportunitiesService $service,
        PrivateTempStore $session,
        SessionManagerInterface $sessionManager
    )
    {
        $this->service = $service;
        $this->session = $session;
        $this->sessionManager = $sessionManager;

        // TODO check if the user is connected when the login is implemented
        if (!isset($_SESSION['session_started'])) {
            $_SESSION['session_started'] = true;
            $this->sessionManager->start();
        }
    }

    /**
     * @param ContainerInterface $container
     *
     * @return SignUpController
     */
    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('opportunities.service'),
            $container->get('user.private_tempstore')->get(OpportunityController::SESSION),
            $container->get('session_manager')
        );
    }

    /**
     * @param string $profileId
     *
     * @return array
     */
    public function step1($profileId)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->redirect('system.403');
        }
        $form = \Drupal::formBuilder()->getForm(SignUpStep1Form::class);
        $form['#action'] = Url::fromRoute(
            'opportunities.eoi.step1',
            [
                'profileId' => $profileId,
            ]
        )->toString();

        $form['firstname']['#value'] = $this->session->get('firstname');
        $form['lastname']['#value'] = $this->session->get('lastname');

        $form['contact_email']['#value'] = $this->session->get('contact_email');
        if (empty($form['contact_email']['#value'])) {
            $form['contact_email']['#value'] = $this->session->get('email');
        }
        $form['contact_phone']['#value'] = $this->session->get('contact_phone');
        if (empty($form['contact_phone']['#value'])) {
            $form['contact_phone']['#value'] = $this->session->get('phone');
        }

        $this->addCheckboxAttributes($form, $this->session->get('newsletter'), 'newsletter');
        $this->addCheckboxAttributes($form, $this->session->get('radiobutton'), 'radiobutton');

        return [
            '#theme' => 'opportunities_sign_up_step1',
            '#form'  => $form,
        ];
    }

    /**
     * @param array  $form
     * @param array  $fields
     * @param string $name
     */
    private function addCheckboxAttributes(&$form, $fields, $name)
    {
        if (empty($fields) === false) {
            foreach ($fields as $field) {
                $form[$name][$field]['#attributes']['checked'] = 'checked';
            }
        }
    }

    /**
     * @param string $profileId
     *
     * @return array
     */
    public function step2($profileId)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->redirect('system.403');
        }
        $form = \Drupal::formBuilder()->getForm(SignUpStep2Form::class);
        $form['#action'] = Url::fromRoute(
            'opportunities.eoi.step2',
            [
                'profileId' => $profileId,
            ]
        )->toString();

        $form['company_name']['#value'] = $this->session->get('company_name');
        $form['company_number']['#value'] = $this->session->get('company_number');
        $form['no_company_number']['#value'] = $this->session->get('no_company_number');
        $form['website']['#value'] = $this->session->get('website');
        $form['company_phone']['#value'] = $this->session->get('company_phone');

        return [
            '#theme' => 'opportunities_sign_up_step2',
            '#form'  => $form,
        ];
    }

    /**
     * @param string $profileId
     *
     * @return array
     */
    public function step3($profileId)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->redirect('system.403');
        }
        $form = \Drupal::formBuilder()->getForm(SignUpStep3Form::class);
        $form['#action'] = Url::fromRoute(
            'opportunities.eoi.step3',
            [
                'profileId' => $profileId,
            ]
        )->toString();

        $form['postcode']['#value'] = $this->session->get('postcode');
        $form['addressone']['#value'] = $this->session->get('addressone');
        $form['addresstwo']['#value'] = $this->session->get('addresstwo');
        $form['city']['#value'] = $this->session->get('city');
        $form['county']['#value'] = $this->session->get('county');

        return [
            '#theme' => 'opportunities_sign_up_step3',
            '#form'  => $form,
        ];
    }

    /**
     * @param string $profileId
     *
     * @return array
     */
    public function review($profileId)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->redirect('system.403');
        }

        $results = $this->service->get($profileId);

        $form = [
            'profile_id'    => $profileId,
            'profile_title' => $results['_source']['title'],

            'other_email' => $this->session->get('other_email'),
            'description' => $this->session->get('description'),
            'interest'    => $this->session->get('interest'),
            'more'        => $this->session->get('more'),
            'phone'       => $this->session->get('phone'),
            'email'       => $this->session->get('email'),

            'firstname'     => $this->session->get('firstname'),
            'lastname'      => $this->session->get('lastname'),
            'contact_email' => $this->session->get('contact_email'),
            'contact_phone' => $this->session->get('contact_phone'),
            'newsletter'    => $this->session->get('newsletter'),

            'company_name'      => $this->session->get('company_name'),
            'company_number'    => $this->session->get('company_number'),
            'no_company_number' => $this->session->get('no_company_number'),
            'website'           => $this->session->get('website'),
            'company_phone'     => $this->session->get('company_phone'),

            'postcode'   => $this->session->get('postcode'),
            'addressone' => $this->session->get('postcode'),
            'addresstwo' => $this->session->get('addresstwo'),
            'city'       => $this->session->get('city'),
            'county'     => $this->session->get('county'),
        ];

        return [
            '#theme' => 'opportunities_sign_up_review',
            '#form'  => $form,
        ];
    }

    /**
     * @param string $profileId
     *
     * @return array
     */
    public function complete($profileId)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->redirect('system.403');
        }

        $results = $this->service->get($profileId);

        $form = [
            'reference_number' => $this->session->get('reference_number'),
            'profile_id'       => $profileId,
            'profile_title'    => $results['_source']['title'],

            'firstname'     => $this->session->get('firstname'),
            'lastname'      => $this->session->get('lastname'),
            'contact_email' => $this->session->get('contact_email'),
            'contact_phone' => $this->session->get('contact_phone'),
            'newsletter'    => $this->session->get('newsletter'),
        ];

        return [
            '#theme' => 'opportunities_sign_up_complete',
            '#form'  => $form,
        ];
    }
}
