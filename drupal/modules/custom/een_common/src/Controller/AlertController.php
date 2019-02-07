<?php
namespace Drupal\een_common\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Element\Html;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\een_common\Service\ContactService;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AlertController extends ControllerBase
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
     *
     * @var OpportunitiesService
     */
    private $oppService;

    /**
     * SignUpController constructor.
     *
     * @param OpportunitiesService    $oppService
     * @param ContactService          $service
     * @param PrivateTempStore        $session
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        OpportunitiesService $oppService,
        ContactService $service,
        PrivateTempStore $session,
        SessionManagerInterface $sessionManager
    )
    {
        $this->oppService = $oppService;
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
            $container->get('contact.service'),
            $container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'),
            $container->get('session_manager')
        );
    }


    public function add(Request $request)
    {

        $data = json_decode(\Drupal::request()->request->all()['data']);
        $xss = new Xss();
        $data->search = $xss->filter($data->search);

        $response = [];

        if($data->email){
            $data->id = $this->service->getContactId($data->email);

            if(!$data->id){
                // no contact - create lead
                $this->service->createLeadV2($data->email);
                $data->id = $this->service->getContactId($data->email);
            }

            $response = $this->oppService->addAlert($data);
        }

        return new JsonResponse(
            [
                $response
            ]
        );
    }



    public function update(Request $request)
    {
        $data = \Drupal::request()->request->all();
        $response = $this->oppService->updateAlert($data);

        return new JsonResponse(
            [
                $response
            ]
        );
    }

    public function remove(Request $request)
    {

        $response = $this->oppService->unsubscribeAlert($request->get('id'));

        return new JsonResponse(
            [
                $response
            ]
        );
    }

    public function unsubscribe(Request $request)
    {
        //get user
        $userId = $this->service->getContactId($request->get('e'));

        if($request->get('t') != md5($userId)){
            return $this->redirect(
                'login',
                array()
            );
        }

        if(!$userId) {
            $alerts = null;
        } else {
            $alerts = $this->service->getAlerts($userId);
        }
        return [
            '#alerts' => $alerts['records'],
            '#theme' => 'alert_unsubscribe',
            '#userdetails'  => null
        ];
    }


}
