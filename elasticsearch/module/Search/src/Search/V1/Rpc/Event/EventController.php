<?php

namespace Search\V1\Rpc\Event;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel;

/**
 * Class EventController
 *
 * @package Search\V1\Rpc\Event
 */
class EventController extends AbstractActionController
{
    /**
     * @return ViewModel
     */
    public function eventAction()
    {
        return new ViewModel([
            'controller' => 'event',
            'success'    => true
        ]);
    }
}
