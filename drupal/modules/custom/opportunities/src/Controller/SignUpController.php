<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\opportunities\Form\SignUpStep1Form;
use Drupal\opportunities\Form\SignUpStep2Form;
use Drupal\opportunities\Form\SignUpStep3Form;
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
        $form['email']['#value'] = $this->session->get('email-company');
        $form['phone']['#value'] = $this->session->get('phone-company');

        $this->addCheckboxAttributes($form, $this->session->get('newsletter'), 'newsletter');
        $this->addCheckboxAttributes($form, $this->session->get('radiobutton'), 'radiobutton');

        return [
            '#theme'      => 'opportunities_sign_up_step1',
            '#form'       => $form,
            '#profile_id' => $profileId,
        ];
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
            '#theme'      => 'opportunities_sign_up_step2',
            '#form'       => $form,
            '#profile_id' => $profileId,
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
            '#theme'      => 'opportunities_sign_up_step3',
            '#form'       => $form,
            '#profile_id' => $profileId,
        ];
    }
}
