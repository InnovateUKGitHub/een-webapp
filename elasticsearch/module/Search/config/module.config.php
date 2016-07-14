<?php

use Search\V1\ElasticSearch\Service\ElasticSearchService;
use Search\V1\ElasticSearch\Factory\ElasticSearchServiceFactory;

return [
    'service_manager'        => [
        'factories' => [
            ElasticSearchService::class => ElasticSearchServiceFactory::class,
        ],
    ],
    'router'                 => [
        'routes' => [
            'een.rpc.opportunity' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/v1/een/opportunity',
                    'defaults' => [
                        'controller' => 'Search\\V1\\Rpc\\Opportunity\\Controller',
                        'action'     => 'opportunity',
                    ],
                ],
            ],
            'een.rpc.event'       => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/v1/een/event',
                    'defaults' => [
                        'controller' => 'Search\\V1\\Rpc\\Event\\Controller',
                        'action'     => 'event',
                    ],
                ],
            ],
        ],
    ],
    'zf-versioning'          => [
        'uri' => [
            0 => 'een.rpc.opportunity',
            1 => 'een.rpc.event',
        ],
    ],
    'zf-rest'                => [],
    'zf-content-negotiation' => [
        'controllers'            => [
            'Search\\V1\\Rpc\\Opportunity\\Controller' => 'Json',
            'Search\\V1\\Rpc\\Event\\Controller'       => 'Json',
        ],
        'accept_whitelist'       => [
            'Search\\V1\\Rpc\\Opportunity\\Controller' => [
                0 => 'application/vnd.een.v1+json',
                1 => 'application/json',
                2 => 'application/*+json',
            ],
            'Search\\V1\\Rpc\\Event\\Controller'       => [
                0 => 'application/vnd.een.v1+json',
                1 => 'application/json',
                2 => 'application/*+json',
            ],
        ],
        'content_type_whitelist' => [
            'Search\\V1\\Rpc\\Opportunity\\Controller' => [
                0 => 'application/vnd.een.v1+json',
                1 => 'application/json',
            ],
            'Search\\V1\\Rpc\\Event\\Controller'       => [
                0 => 'application/vnd.een.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'zf-hal'                 => [
        'metadata_map' => [],
    ],
    'zf-content-validation'  => [
        'Search\\V1\\Rpc\\Opportunity\\Controller' => [
            'input_filter' => 'Search\\V1\\Rpc\\Opportunity\\Validator',
        ],
    ],
    'input_filter_specs'     => [
        'Search\\V1\\Rest\\Opportunity\\Validator' => [
            0 => [
                'required'          => true,
                'validators'        => [
                    0 => [
                        'name'    => 'Zend\\I18n\\Validator\\IsInt',
                        'options' => [],
                    ],
                ],
                'filters'           => [
                    0 => [
                        'name'    => 'Zend\\Filter\\ToInt',
                        'options' => [],
                    ],
                ],
                'name'              => 'start',
                'allow_empty'       => false,
                'continue_if_empty' => false,
            ],
            1 => [
                'required'          => true,
                'validators'        => [
                    0 => [
                        'name'    => 'Zend\\I18n\\Validator\\IsInt',
                        'options' => [],
                    ],
                ],
                'filters'           => [
                    0 => [
                        'name'    => 'Zend\\Filter\\ToInt',
                        'options' => [],
                    ],
                ],
                'name'              => 'length',
                'allow_empty'       => true,
                'continue_if_empty' => true,
            ],
        ],
        'Search\\V1\\Rpc\\Opportunity\\Validator'  => [
            0 => [
                'required'   => true,
                'validators' => [],
                'filters'    => [],
                'name'       => 'start',
            ],
            1 => [
                'required'   => true,
                'validators' => [],
                'filters'    => [],
                'name'       => 'length',
            ],
        ],
    ],
    'controllers'            => [
        'factories' => [
            'Search\\V1\\Rpc\\Opportunity\\Controller' => 'Search\\V1\\Rpc\\Opportunity\\OpportunityControllerFactory',
            'Search\\V1\\Rpc\\Event\\Controller'       => 'Search\\V1\\Rpc\\Event\\EventControllerFactory',
        ],
    ],
    'zf-rpc'                 => [
        'Search\\V1\\Rpc\\Opportunity\\Controller' => [
            'service_name' => 'Opportunity',
            'http_methods' => [
                0 => 'GET',
            ],
            'route_name'   => 'een.rpc.opportunity',
        ],
        'Search\\V1\\Rpc\\Event\\Controller'       => [
            'service_name' => 'Event',
            'http_methods' => [
                0 => 'GET',
            ],
            'route_name'   => 'een.rpc.event',
        ],
    ],
];
