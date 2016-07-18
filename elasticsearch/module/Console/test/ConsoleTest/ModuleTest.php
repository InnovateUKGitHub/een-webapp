<?php

namespace ConsoleTest;

use Console\Module;
use Zend\Console\Adapter\AdapterInterface;
use Console\Factory\Controller\GenerateControllerFactory;
use Console\Controller\GenerateController;
use Console\Factory\Service\GenerateServiceFactory;
use Console\Service\GenerateService;
use Console\Factory\Service\DeleteServiceFactory;
use Console\Service\DeleteService;
use Console\Factory\Service\ConnectionServiceFactory;
use Console\Service\ConnectionService;
use Console\Factory\Service\HttpServiceFactory;
use Console\Service\HttpService;

/**
 * @covers Console\Module
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigIsCorrect()
    {
        $module = new Module();

        self::assertEquals([
            'controllers'     => [
                'factories' => [
                    GenerateController::class => GenerateControllerFactory::class,
                ],
            ],
            'service_manager' => [
                'factories' => [
                    GenerateService::class   => GenerateServiceFactory::class,
                    DeleteService::class     => DeleteServiceFactory::class,
                    ConnectionService::class => ConnectionServiceFactory::class,
                    HttpService::class       => HttpServiceFactory::class
                ],
            ],
            'console'         => [
                'router' => [
                    'routes' => [
                        'generate-data' => [
                            'options' => [
                                'route'    => 'generate [--index=<index>] [--number=<number>]',
                                'constraints' => [
                                    'index' => '[opportunity|event|all]',
                                ],
                                'defaults' => [
                                    'controller' => GenerateController::class,
                                    'action'     => 'generate',
                                ],
                            ],
                        ],
                        'delete-data' => [
                            'options' => [
                                'route'    => 'delete [--index=<index>]',
                                'constraints' => [
                                    'index' => '[opportunity|event|all]',
                                ],
                                'defaults' => [
                                    'controller' => GenerateController::class,
                                    'action'     => 'delete',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $module->getConfig());
    }

    public function testConsoleUsageIsCorrect()
    {
        $module = new Module();

        /** @var \PHPUnit_Framework_MockObject_MockObject|AdapterInterface $console */
        $console = self::getMock(AdapterInterface::class, [], [], '', false);

        self::assertEquals([
            'generate [--index=<index>] [--number=<number>]' => 'Generate random data into elasticSearch for test purpose',
            ['--index', 'Index to generate. [opportunity|event|all] (default: all)'],
            ['--number', 'Number of documents to generate. (default: 10)'],
            'delete [--index=<index>]' => 'Delete elasticSearch index type',
            ['--index', 'Index to delete. [opportunity|event|all] (default: all)'],
        ], $module->getConsoleUsage($console));
    }
}
