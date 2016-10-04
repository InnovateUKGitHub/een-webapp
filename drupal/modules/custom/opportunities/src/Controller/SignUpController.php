<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\opportunities\Form\SignUpStep1Form;
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
//        if (!$this->session->get('isLoggedIn')) {
//            return $this->redirect('system.403');
//        }
        $form = \Drupal::formBuilder()->getForm(SignUpStep1Form::class);

        $form['firstname']['#value'] = $this->session->get('firstname');
        $form['lastname']['#value'] = $this->session->get('lastname');
        $form['email']['#value'] = $this->session->get('email-company');
        $form['phone']['#value'] = $this->session->get('phone-company');
        $form['newsletter']['#value'] = $this->session->get('newsletter');
        $form['radiobutton']['#value'] = $this->session->get('radiobutton');

        return [
            '#theme'      => 'opportunities_sign_up_step1',
            '#form'       => $form,
            '#profile_id' => $profileId,
        ];
    }
}
