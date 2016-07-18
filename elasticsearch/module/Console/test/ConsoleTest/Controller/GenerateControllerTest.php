<?php

namespace ConsoleTest\Controller;

use Console\Controller\GenerateController;
use Console\Service\DeleteService;
use Console\Service\GenerateService;
use ConsoleTest\Bootstrap;
use Zend\Console\Exception\BadMethodCallException;
use Zend\Console\Exception\InvalidArgumentException;
use Zend\Console\Request;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface;

/**
 * @covers Console\Controller\GenerateController
 */
class GenerateControllerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|GenerateService */
    private $generateServiceMock;
    /** @var \PHPUnit_Framework_MockObject_MockObject|DeleteService */
    private $deleteServiceMock;

    public function Setup()
    {
        $this->generateServiceMock = self::getMock(GenerateService::class, [], [], '', false);
        $this->deleteServiceMock = self::getMock(DeleteService::class, [], [], '', false);
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage This is a console tool only
     */
    public function testGenerateActionNotConsole()
    {
        $controller = $this->buildController(['action' => 'generate']);

        $controller->dispatch($controller->getRequest());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The index enter is not valid
     */
    public function testGenerateActionInvalidIndex()
    {
        $controller = $this->buildController(['index' => 'invalidIndex', 'action' => 'generate']);

        $request = new Request();
        $controller->dispatch($request);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The number enter is not valid
     */
    public function testGenerateActionInvalidNumber()
    {
        $controller = $this->buildController(['number' => 'invalidNumber', 'action' => 'generate']);

        $request = new Request();
        $controller->dispatch($request);
    }

    public function testGenerateAction()
    {
        $controller = $this->buildController(['action' => 'generate']);

        $request = new Request();
        self::assertEquals(['generate' => 'success'], $controller->dispatch($request));
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage This is a console tool only
     */
    public function testDeleteActionNotConsole()
    {
        $controller = $this->buildController(['action' => 'delete']);

        $controller->dispatch($controller->getRequest());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The index enter is not valid
     */
    public function testDeleteActionInvalidIndex()
    {
        $controller = $this->buildController(['index' => 'invalidIndex', 'action' => 'delete']);

        $request = new Request();
        $controller->dispatch($request);
    }

    public function testDeleteAction()
    {
        $controller = $this->buildController(['action' => 'delete']);

        $request = new Request();
        self::assertEquals(['delete' => 'success'], $controller->dispatch($request));
    }

    private function buildController($routMatch)
    {
        $controller = new GenerateController($this->generateServiceMock, $this->deleteServiceMock);

        $serviceManager = Bootstrap::getServiceManager();
        /** @var RouteStackInterface $router */
        $router = $serviceManager->get('HttpRouter');
        $routeMatch = new RouteMatch($routMatch);

        $event = new MvcEvent();
        $event->setRouter($router);
        $event->setRouteMatch($routeMatch);

        $pluginManager = new PluginManager();
        $controller->setEvent($event);
        $controller->setPluginManager($pluginManager);
        $controller->setServiceLocator($serviceManager);

        return $controller;
    }
}
