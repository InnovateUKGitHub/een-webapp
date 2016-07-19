<?php

namespace ConsoleTest\Controller;

use Search\V1\ElasticSearch\Service\ElasticSearchService;
use Search\V1\Rpc\Event\EventController;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use ZF\ContentNegotiation\ViewModel;

/**
 * @covers Search\V1\Rpc\Event\EventController
 */
class EventControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testEventAction()
    {
        $elasticSearchServiceMock = self::getMock(ElasticSearchService::class, [], [], '', false);
        $inputFilterMock = self::getMock('ZF\ContentValidation\InputFilter', ['getValues'], [], '', false);

        $elasticSearchServiceMock->expects(self::once())
            ->method('searchEvent')
            ->with(['params' => 'myParams']);

        $inputFilterMock->expects(self::once())
            ->method('getValues')
            ->willReturn(['params' => 'myParams']);

        $controller = new EventController($elasticSearchServiceMock);
        $routeMatch = new RouteMatch(['action' => 'event']);

        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);
        $event->setParam('ZF\ContentValidation\InputFilter', $inputFilterMock);

        $controller->setEvent($event);
        self::assertInstanceOf(ViewModel::class, $controller->dispatch($controller->getRequest()));
    }

}
