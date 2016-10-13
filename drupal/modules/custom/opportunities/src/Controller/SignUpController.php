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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class SignUpController extends ControllerBase
{
    const VALUE = 'value';

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
            $container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'),
            $container->get('session_manager')
        );
    }

    /**
     * @param string  $profileId
     * @param Request $request
     *
     * @return array
     */
    public function step1($profileId, Request $request)
    {
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('eoi', 'opportunities.details', $profileId, $this->session->get('token'))) !== null) {
            return $redirect;
        }

        $form = \Drupal::formBuilder()->getForm(SignUpStep1Form::class);
        $form['#action'] = Url::fromRoute(
            'opportunities.eoi.step1',
            [
                'profileId' => $profileId,
            ]
        )->toString();

        if ($request->isMethod(Request::METHOD_GET)) {
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
        }

        return [
            '#theme' => 'opportunities_sign_up_step1',
            '#form'  => $form,
        ];
    }

    /**
     * @throws UnauthorizedHttpException
     */
    private function isLoggedIn()
    {
        if (!$this->session->get('isLoggedIn')) {
            throw new UnauthorizedHttpException('');
        }
    }

    /**
     * @param string $step
     * @param string $redirect
     * @param string $profileId
     * @param null   $token
     *
     * @return null|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function isStepValid($step, $redirect, $profileId, $token = null)
    {
        if (!$this->session->get($step)) {
            $params = ['profileId' => $profileId];
            if ($token !== null) {
                $params['token'] = $token;
            }

            return $this->redirect(
                $redirect,
                $params
            );
        }

        return null;
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
     * @param string  $profileId
     * @param Request $request
     *
     * @return array
     */
    public function step2($profileId, Request $request)
    {
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('step1', 'opportunities.eoi.step1', $profileId)) !== null) {
            return $redirect;
        }

        $form = \Drupal::formBuilder()->getForm(SignUpStep2Form::class);
        $form['#action'] = Url::fromRoute(
            'opportunities.eoi.step2',
            [
                'profileId' => $profileId,
            ]
        )->toString();

        if ($request->isMethod(Request::METHOD_GET)) {
            $form['company_name']['#value'] = $this->session->get('company_name');
            $form['company_number']['#value'] = $this->session->get('company_number');
            $form['website']['#value'] = $this->session->get('website');
            $form['company_phone']['#value'] = $this->session->get('company_phone');
        }

        return [
            '#theme' => 'opportunities_sign_up_step2',
            '#form'  => $form,
        ];
    }

    /**
     * @param string  $profileId
     * @param Request $request
     *
     * @return array
     */
    public function step3($profileId, Request $request)
    {
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('step2', 'opportunities.eoi.step2', $profileId)) !== null) {
            return $redirect;
        }

        $form = \Drupal::formBuilder()->getForm(SignUpStep3Form::class);
        $form['#action'] = Url::fromRoute(
            'opportunities.eoi.step3',
            [
                'profileId' => $profileId,
            ]
        )->toString();

        if ($request->isMethod(Request::METHOD_GET)) {
            $form['postcode']['#value'] = $this->session->get('postcode');
            $form['addressone']['#value'] = $this->session->get('addressone');
            $form['addresstwo']['#value'] = $this->session->get('addresstwo');
            $form['city']['#value'] = $this->session->get('city');
        }

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
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('step3', 'opportunities.eoi.step3', $profileId)) !== null) {
            return $redirect;
        }

        $results = $this->service->get($profileId);
        $form = $this->getSession($profileId, $results['_source']['title']);

        return [
            '#theme' => 'opportunities_sign_up_review',
            '#form'  => $form,
        ];
    }

    /**
     * @param string $profileId
     * @param string $profileTitle
     *
     * @return array
     */
    private function getSession($profileId, $profileTitle)
    {
        return [
            'profile_id'    => $profileId,
            'profile_title' => $profileTitle,
            'reference_number' => $this->session->get('reference_number'),
            
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

            'company_name'   => $this->session->get('company_name'),
            'company_number' => $this->session->get('company_number'),
            'website'        => $this->session->get('website'),
            'company_phone'  => $this->session->get('company_phone'),

            'postcode'   => $this->session->get('postcode'),
            'addressone' => $this->session->get('addressone'),
            'addresstwo' => $this->session->get('addresstwo'),
            'city'       => $this->session->get('city'),
        ];
    }

    /**
     * @param string $profileId
     *
     * @return array
     */
    public function complete($profileId)
    {
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('step3', 'opportunities.eoi.step3', $profileId)) !== null) {
            return $redirect;
        }

        $results = $this->service->get($profileId);
        $form = $this->getSession($profileId, $results['_source']['title']);

        $this->service->convertLead($form);

        return [
            '#theme' => 'opportunities_sign_up_complete',
            '#form'  => $form,
        ];
    }

    /**
     * @param string  $field
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update($field, Request $request)
    {
        $value = $request->get(self::VALUE);

        $this->session->set($field, $value);

        return new JsonResponse(
            [
                'success' => true,
                'params'  => $value,
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function companies(Request $request)
    {
        return new JsonResponse($this->service->getCompaniesList($request->get('q')));
    }
}
