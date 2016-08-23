<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\opportunities\Form\ExpressionOfInterestForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OpportunityController extends ControllerBase
{
    const PAGE_NUMBER = 'page';

    const RESULT_PER_PAGE = 'resultPerPage';

    const SEARCH = 'search';

    const OPPORTUNITY_TYPE = 'opportunity_type';

    /**
     * @var OpportunitiesService
     */
    private $service;

    /**
     * OpportunityController constructor.
     *
     * @param OpportunitiesService $service
     */
    public function __construct(OpportunitiesService $service)
    {
        $this->service = $service;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return OpportunityController
     */
    public static function create(ContainerInterface $container)
    {
        return new self($container->get('opportunities.service'));
    }

    /**
     * @param string  $profileId
     * @param Request $request
     *
     * @return array
     */
    public function index($profileId, Request $request)
    {
        $search = $request->query->get(self::SEARCH);
        $types = $request->query->get(self::OPPORTUNITY_TYPE);

        $results = $this->service->get($profileId);

        $form = \Drupal::formBuilder()->getForm(ExpressionOfInterestForm::class);

        return [
            '#theme'            => 'opportunities_details',
            '#form'             => $form,
            '#opportunity'      => $results,
            '#search'           => $search,
            '#opportunity_type' => $types,
        ];
    }

    /**
     * @return JsonResponse
     */
    public function form()
    {
        $form = \Drupal::formBuilder()->getForm(ExpressionOfInterestForm::class);


        return new JsonResponse(['form' => $form]);
    }
}
