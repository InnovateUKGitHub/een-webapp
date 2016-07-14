<?php

use Admin\V1\Rest\Delete\DeleteResourceFactory;
use Admin\V1\Rest\Delete\DeleteResource;

return [
    'service_manager'        => [
        'factories' => [
            DeleteResource::class => DeleteResourceFactory::class,
        ],
    ],
    'router'                 => [
        'routes' => [
            'admin.rest.delete' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/delete[/:delete_id]',
                    'defaults' => [
                        'controller' => 'Admin\\V1\\Rest\\Delete\\Controller',
                    ],
                ],
            ],
        ],
    ],
    'zf-versioning'          => [
        'uri' => [
            0 => 'admin.rest.delete',
        ],
    ],
    'zf-rest'                => [
        'Admin\\V1\\Rest\\Delete\\Controller' => [
            'listener'                   => 'Admin\\V1\\Rest\\Delete\\DeleteResource',
            'route_name'                 => 'admin.rest.delete',
            'route_identifier_name'      => 'delete_id',
            'collection_name'            => 'delete',
            'entity_http_methods'        => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods'    => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size'                  => 25,
            'page_size_param'            => null,
            'entity_class'               => 'Admin\\V1\\Rest\\Delete\\DeleteEntity',
            'collection_class'           => 'Admin\\V1\\Rest\\Delete\\DeleteCollection',
            'service_name'               => 'delete',
        ],
    ],
    'zf-content-negotiation' => [
        'controllers'            => [
            'Admin\\V1\\Rest\\Delete\\Controller' => 'HalJson',
        ],
        'accept_whitelist'       => [
            'Admin\\V1\\Rest\\Delete\\Controller' => [
                0 => 'application/vnd.admin.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
        ],
        'content_type_whitelist' => [
            'Admin\\V1\\Rest\\Delete\\Controller' => [
                0 => 'application/vnd.admin.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'zf-hal'                 => [
        'metadata_map' => [
            'Admin\\V1\\Rest\\Delete\\DeleteEntity'     => [
                'entity_identifier_name' => 'id',
                'route_name'             => 'admin.rest.delete',
                'route_identifier_name'  => 'delete_id',
                'hydrator'               => 'Zend\\Hydrator\\ArraySerializable',
            ],
            'Admin\\V1\\Rest\\Delete\\DeleteCollection' => [
                'entity_identifier_name' => 'id',
                'route_name'             => 'admin.rest.delete',
                'route_identifier_name'  => 'delete_id',
                'is_collection'          => true,
            ],
        ],
    ],
    'zf-content-validation'  => [
        'Admin\\V1\\Rest\\Delete\\Controller' => [
            'input_filter' => 'Admin\\V1\\Rest\\Delete\\Validator',
        ],
    ],
    'input_filter_specs'     => [
        'Admin\\V1\\Rest\\Delete\\Validator' => [
            0 => [
                'required'   => true,
                'validators' => [
                    0 => [
                        'name'    => 'Zend\\Validator\\InArray',
                        'options' => [
                            'strict'   => \Zend\Validator\InArray::COMPARE_STRICT,
                            'haystack' => [
                                'opportunity',
                                'event',
                                'all',
                            ],
                        ],
                    ],
                ],
                'filters'    => [],
                'name'       => 'index',
            ],
        ],
    ],
];
