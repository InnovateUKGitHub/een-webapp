<?php

namespace EEN\V1\Rpc\Event;

/**
 * Class EventControllerFactory
 *
 * @package EEN\V1\Rpc\Event
 */
class EventControllerFactory
{
    /**
     * @param $controllers
     *
     * @return EventController
     */
    public function __invoke($controllers)
    {
        return new EventController();
    }
}
