<?php
namespace Drupal\events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\events\Service\EventsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class EventsController extends ControllerBase
{
    const PAGE_NUMBER = 'page';

    const RESULT_PER_PAGE = 'resultPerPage';
    const DEFAULT_RESULT_PER_PAGE = 10;

    const SEARCH = 'search';

    /**
     * @var EventsService
     */
    private $service;

    /**
     * EventsController constructor.
     *
     * @param EventsService $service
     */
    public function __construct(EventsService $service)
    {
        $this->service = $service;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return EventsController
     */
    public static function create(ContainerInterface $container)
    {
        return new self($container->get('events.service'));
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function index(Request $request)
    {

        $page = $request->query->get(self::PAGE_NUMBER, 1);
        $resultPerPage = $request->query->get(self::RESULT_PER_PAGE, self::DEFAULT_RESULT_PER_PAGE);

        $results = $this->service->search($page, $resultPerPage);

        return [
            '#theme'         => 'events_search',
            '#results'       => isset($results['results']) ? $results['results'] : null,
            '#total'         => isset($results['total']) ? $results['total'] : null,
            '#pageTotal'     => isset($results['results']) ? (int)ceil($results['total'] / $resultPerPage) : null,
            '#page'          => $page,
            '#resultPerPage' => $resultPerPage,
            '#route'         => 'events.search',
        ];
    }
}
