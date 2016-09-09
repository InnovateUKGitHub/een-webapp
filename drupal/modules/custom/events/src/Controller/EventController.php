<?php
namespace Drupal\events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\events\Service\EventsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class EventController extends ControllerBase
{
    const PAGE_NUMBER = 'page';

    const RESULT_PER_PAGE = 'resultPerPage';
    const DEFAULT_RESULT_PER_PAGE = 20;

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
     * @return EventController
     */
    public static function create(ContainerInterface $container)
    {
        return new self($container->get('events.service'));
    }

    /**
     * @param string  $eventId
     * @param Request $request
     *
     * @return array
     */
    public function index($eventId, Request $request)
    {
        $page = $request->query->get(self::PAGE_NUMBER, 1);
        $results = $this->service->get($eventId);

        return [
            '#theme' => 'events_details',
            '#event' => $results,
            '#page'  => $page,
            '#route' => 'events.search',
        ];
    }
}
