<?php
namespace Drupal\een_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\een_common\Form\SignUp\SignUpStep1Form;
use Drupal\een_common\Form\SignUp\SignUpStep2Form;
use Drupal\een_common\Form\SignUp\SignUpStep3Form;
use Drupal\een_common\Service\ContactService;
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
     * @var ContactService
     */
    private $service;

    /**
     * SignUpController constructor.
     *
     * @param ContactService          $service
     * @param PrivateTempStore        $session
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        ContactService $service,
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
            $container->get('contact.service'),
            $container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'),
            $container->get('session_manager')
        );
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
     * @param string $id
     * @param null   $token
     *
     * @return null|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function isStepValid($step, $redirect, $id, $token = null)
    {
        if (!$this->session->get($step)) {
            if ($redirect === 'opportunities.details') {
                $params = ['profileId' => $id];
            } else if ($redirect === 'events.details') {
                $params = ['eventId' => $id];
            } else {
                $params = ['id' => $id];
            }
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
     * @param string $id
     * @param string $title
     *
     * @return array
     */
    private function getSession($id, $title)
    {
        return [
            'id'               => $id,
            'title'            => $title,
            'reference_number' => $this->session->get('reference_number'),

            'dietary'     => $this->session->get('dietary'),
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
     * @param string  $type
     * @param string  $id
     * @param Request $request
     *
     * @return array
     */
    public function step1($type, $id, Request $request)
    {
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('email-verification', $type . '.details', $type, $this->session->get('token'))) !== null) {
            return $redirect;
        }

        $form = \Drupal::formBuilder()->getForm(SignUpStep1Form::class);
        $form['#action'] = Url::fromRoute('sign-up.step1', ['id' => $id, 'type' => $type])->toString();

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
            '#theme' => 'sign_up_step1',
            '#form'  => $form,
            '#type'  => $type,
        ];
    }

    /**
     * @param string  $type
     * @param string  $id
     * @param Request $request
     *
     * @return array
     */
    public function step2($type, $id, Request $request)
    {
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('step1', $type . '.step1', $id)) !== null) {
            return $redirect;
        }

        $form = \Drupal::formBuilder()->getForm(SignUpStep2Form::class);
        $form['#action'] = Url::fromRoute('sign-up.step2', ['id' => $id, 'type' => $type])->toString();

        if ($request->isMethod(Request::METHOD_GET)) {
            $form['company_name']['#value'] = $this->session->get('company_name');
            $form['company_number']['#value'] = $this->session->get('company_number');
            $form['website']['#value'] = $this->session->get('website');
            $form['company_phone']['#value'] = $this->session->get('company_phone');
        }

        return [
            '#theme' => 'sign_up_step2',
            '#form'  => $form,
            '#type'  => $type,
        ];
    }

    /**
     * @param string  $type
     * @param string  $id
     * @param Request $request
     *
     * @return array
     */
    public function step3($type, $id, Request $request)
    {
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('step2', $type . '.step2', $id)) !== null) {
            return $redirect;
        }

        $form = \Drupal::formBuilder()->getForm(SignUpStep3Form::class);
        $form['#action'] = Url::fromRoute('sign-up.step3', ['id' => $id, 'type' => $type])->toString();

        if ($request->isMethod(Request::METHOD_GET)) {
            $form['postcode']['#value'] = $this->session->get('postcode');
            $form['addressone']['#value'] = $this->session->get('addressone');
            $form['addresstwo']['#value'] = $this->session->get('addresstwo');
            $form['city']['#value'] = $this->session->get('city');
        }

        return [
            '#theme' => 'sign_up_step3',
            '#form'  => $form,
            '#type'  => $type,
        ];
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return array
     */
    public function review($type, $id)
    {
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('step3', $type . '.step3', $id)) !== null) {
            return $redirect;
        }

        $results = $this->service->get($type, $id);
        $form = $this->getSession($id, $results['_source']['title']);

        return [
            '#theme' => $type == 'opportunities' ? 'sign_up_review' : 'sign_up_review_event',
            '#form'  => $form,
            '#type'  => $type,
        ];
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return array
     */
    public function complete($type, $id)
    {
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('step3', $type . '.step3', $id)) !== null) {
            return $redirect;
        }

        $results = $this->service->get($type, $id);
        $form = $this->getSession($id, $results['_source']['title']);

        $user = $this->service->convertLead($form);

        if ($type === 'events') {
            $data = [
                'contact' => $user['Id'],
                'event'   => $id,
                'dietary' => $form['dietary'],
            ];
            $this->service->registerToEvent($data);
        }

        return [
            '#theme' => $type == 'opportunities' ? 'sign_up_complete' : 'sign_up_complete_event',
            '#form'  => $form,
            '#type'  => $type,
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
