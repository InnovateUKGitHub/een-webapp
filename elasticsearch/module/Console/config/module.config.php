<?php

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

return [
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
];
