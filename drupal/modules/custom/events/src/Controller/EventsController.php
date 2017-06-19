<?php
namespace Drupal\events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\events\Service\EventsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EventsController extends ControllerBase
{
    /**
     * @var EventsService
     */
    private $service;

    /**
     * EventsController constructor.
     *
     * @param EventsService|object $service
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
        $data = $this->service->getEvents($request);

        return [
            '#theme'         => 'events_search',
            '#route'         => 'events.search',
            '#form'          => $data['form'],
            '#search'        => $data['search'],
            '#date_type'     => $data['date_type'],
            '#date_from'     => $data['date_from'],
            '#date_to'       => $data['date_to'],
            '#country'       => $data['country'],
            '#page'          => $data['page'],
            '#resultPerPage' => $data['resultPerPage'],
            '#pageTotal'     => $data['pageTotal'],
            '#total'         => $data['total'],
            '#results'       => $data['results'],
        ];
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajax(Request $request)
    {
        $data = $this->service->getEvents($request);

        return new JsonResponse(
            [
                'route'         => 'events.search',
                'search'        => $data['search'],
                'date_type'     => $data['date_type'],
                'date_from'     => $data['date_from'],
                'date_to'       => $data['date_to'],
                'country'       => $data['country'],
                'page'          => $data['page'],
                'resultPerPage' => $data['resultPerPage'],
                'pageTotal'     => $data['pageTotal'],
                'total'         => $data['total'],
                'results'       => $data['results'],
            ]
        );
    }
}
