<?php

namespace Search\V1\Rpc\Event;

/**
 * Class EventControllerFactory
 *
 * @package Search\V1\Rpc\Event
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
