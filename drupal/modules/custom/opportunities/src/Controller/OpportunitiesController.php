<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\opportunities\Form\OpportunitiesForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class OpportunitiesController extends ControllerBase
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
        $form = \Drupal::formBuilder()->getForm(OpportunitiesForm::class);

        $page = $request->query->get(self::PAGE_NUMBER, 1);
        $resultPerPage = $request->query->get(self::RESULT_PER_PAGE, 10);
        $search = $request->query->get(self::SEARCH);
        $types = $request->query->get(self::OPPORTUNITY_TYPE);

        if ($search !== null) {
            $results = $this->service->search($form, $search, $types, $page, $resultPerPage);
        }

        return [
            '#theme'            => 'opportunities_search',
            '#form'             => $form,
            '#search'           => $search,
            '#opportunity_type' => $types,
            '#results'          => isset($results['results']) ? $results['results'] : null,
            '#total'            => isset($results['total']) ? $results['total'] : null,
            '#pageTotal'        => isset($results['results']) ? (int)ceil($results['total'] / $resultPerPage) : null,
            '#page'             => $page,
            '#resultPerPage'    => $resultPerPage,
            '#route'            => 'opportunities.search',
        ];
    }
}
