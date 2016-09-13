<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\opportunities\Form\OpportunitiesForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OpportunitiesController extends ControllerBase
{
    const PAGE_NUMBER = 'page';

    const RESULT_PER_PAGE = 'resultPerPage';
    const DEFAULT_RESULT_PER_PAGE = 20;

    const SEARCH = 'search';

    const OPPORTUNITY_TYPE = 'opportunity_type';

    /**
     * @var OpportunitiesService
     */
    private $service;

    /**
     * OpportunitiesController constructor.
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
     * @return OpportunitiesController
     */
    public static function create(ContainerInterface $container)
    {
        return new self($container->get('opportunities.service'));
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function index(Request $request)
    {
        $data = $this->getOpportunities($request);

        return [
            '#theme'            => 'opportunities_search',
            '#form'             => $data['form'],
            '#search'           => $data['search'],
            '#opportunity_type' => $data['types'],
            '#results'          => isset($data['results']['results']) ? $data['results']['results'] : null,
            '#total'            => isset($data['results']['total']) ? $data['results']['total'] : null,
            '#pageTotal'        => isset($data['results']['results']) ? (int)ceil($data['results']['total'] / $data['resultPerPage']) : null,
            '#page'             => $data['page'],
            '#resultPerPage'    => $data['resultPerPage'],
            '#route'            => 'opportunities.search',
        ];
    }

    private function getOpportunities(Request $request)
    {
        $form = \Drupal::formBuilder()->getForm(OpportunitiesForm::class);

        $page = $request->query->get(self::PAGE_NUMBER, 1);
        $resultPerPage = $request->query->get(self::RESULT_PER_PAGE, self::DEFAULT_RESULT_PER_PAGE);
        $search = $request->query->get(self::SEARCH);
        $types = $request->query->get(self::OPPORTUNITY_TYPE);

        $results = $this->service->search($form, $search, $types, $page, $resultPerPage);

        return [
            'form'          => $form,
            'results'       => $results,
            'page'          => $page,
            'resultPerPage' => $resultPerPage,
            'search'        => $search,
            'types'         => $types,
        ];
    }

    public function ajax(Request $request)
    {

        $data = $this->getOpportunities($request);

        return new JsonResponse(
            [
                'search'           => $data['search'],
                'opportunity_type' => $data['types'],
                'results'          => isset($data['results']['results']) ? $data['results']['results'] : null,
                'total'            => isset($data['results']['total']) ? $data['results']['total'] : null,
                'pageTotal'        => isset($data['results']['results']) ? (int)ceil($data['results']['total'] / $data['resultPerPage']) : null,
                'page'             => $data['page'],
                'resultPerPage'    => $data['resultPerPage'],
                'route'            => 'opportunities.search',
            ]
        );
    }
}
