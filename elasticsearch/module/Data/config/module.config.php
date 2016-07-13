<?php

use Data\Factory\Controller\GenerateControllerFactory;
use Data\Controller\GenerateController;
use Data\Factory\Service\GenerateServiceFactory;
use Data\Service\GenerateService;
use Data\Factory\Service\DeleteServiceFactory;
use Data\Service\DeleteService;
use Data\Factory\Service\ConnectionServiceFactory;
use Data\Service\ConnectionService;
use Data\Factory\Service\HttpServiceFactory;
use Data\Service\HttpService;

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
