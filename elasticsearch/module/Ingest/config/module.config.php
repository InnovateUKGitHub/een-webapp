<?php

use Ingest\V1\Rest\Delete\DeleteResourceFactory;
use Ingest\V1\Rest\Delete\DeleteResource;
use Ingest\V1\Rest\Event\EventResourceFactory;
use Ingest\V1\Rest\Event\EventResource;
use Ingest\V1\Rest\Opportunity\OpportunityResourceFactory;
use Ingest\V1\Rest\Opportunity\OpportunityResource;
use Ingest\V1\Service\IndexService;
use Ingest\V1\Factory\IndexServiceFactory;

return [
    'service_manager'        => [
        'factories' => [
            DeleteResource::class      => DeleteResourceFactory::class,
            EventResource::class       => EventResourceFactory::class,
            OpportunityResource::class => OpportunityResourceFactory::class,
            IndexService::class        => IndexServiceFactory::class,
        ],
    ],
    'router'                 => [
        'routes' => [
            'ingest.rest.delete'      => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/delete[/:delete_id]',
                    'defaults' => [
                        'controller' => 'Ingest\\V1\\Rest\\Delete\\Controller',
                    ],
                ],
            ],
            'ingest.rest.opportunity' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/opportunity[/:opportunity_id]',
                    'defaults' => [
                        'controller' => 'Ingest\\V1\\Rest\\Opportunity\\Controller',
                    ],
                ],
            ],
            'ingest.rest.event'       => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/event[/:event_id]',
                    'defaults' => [
                        'controller' => 'Ingest\\V1\\Rest\\Event\\Controller',
                    ],
                ],
            ],
        ],
    ],
    'zf-versioning'          => [
        'uri' => [
            0 => 'ingest.rest.delete',
            1 => 'ingest.rest.opportunity',
            2 => 'ingest.rest.event',
        ],
    ],
    'zf-rest'                => [
        'Ingest\\V1\\Rest\\Delete\\Controller'      => [
            'listener'                   => 'Ingest\\V1\\Rest\\Delete\\DeleteResource',
            'route_name'                 => 'ingest.rest.delete',
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
            'service_name'               => 'Delete',
        ],
        'Ingest\\V1\\Rest\\Opportunity\\Controller' => [
            'listener'                   => 'Ingest\\V1\\Rest\\Opportunity\\OpportunityResource',
            'route_name'                 => 'ingest.rest.opportunity',
            'route_identifier_name'      => 'opportunity_id',
            'collection_name'            => 'opportunity',
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
            'service_name'               => 'Opportunity',
        ],
        'Ingest\\V1\\Rest\\Event\\Controller'       => [
            'listener'                   => 'Ingest\\V1\\Rest\\Event\\EventResource',
            'route_name'                 => 'ingest.rest.event',
            'route_identifier_name'      => 'event_id',
            'collection_name'            => 'event',
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
            'service_name'               => 'Event',
        ],
    ],
    'zf-content-negotiation' => [
        'controllers'            => [
            'Ingest\\V1\\Rest\\Delete\\Controller'      => 'HalJson',
            'Ingest\\V1\\Rest\\Opportunity\\Controller' => 'HalJson',
            'Ingest\\V1\\Rest\\Event\\Controller'       => 'HalJson',
        ],
        'accept_whitelist'       => [
            'Ingest\\V1\\Rest\\Delete\\Controller'      => [
                0 => 'application/vnd.admin.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'Ingest\\V1\\Rest\\Opportunity\\Controller' => [
                0 => 'application/vnd.ingest.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'Ingest\\V1\\Rest\\Event\\Controller'       => [
                0 => 'application/vnd.ingest.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
        ],
        'content_type_whitelist' => [
            'Ingest\\V1\\Rest\\Delete\\Controller'      => [
                0 => 'application/vnd.admin.v1+json',
                1 => 'application/json',
            ],
            'Ingest\\V1\\Rest\\Opportunity\\Controller' => [
                0 => 'application/vnd.ingest.v1+json',
                1 => 'application/json',
            ],
            'Ingest\\V1\\Rest\\Event\\Controller'       => [
                0 => 'application/vnd.ingest.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'zf-content-validation'  => [
        'Ingest\\V1\\Rest\\Delete\\Controller'      => [
            'input_filter' => 'Ingest\\V1\\Rest\\Delete\\Validator',
        ],
        'Ingest\\V1\\Rest\\Opportunity\\Controller' => [
            'input_filter' => 'Ingest\\V1\\Rest\\Opportunity\\Validator',
        ],
        'Ingest\\V1\\Rest\\Event\\Controller'       => [
            'input_filter' => 'Ingest\\V1\\Rest\\Event\\Validator',
        ],
    ],
    'input_filter_specs'     => [
        'Ingest\\V1\\Rest\\Delete\\Validator'      => [
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
        'Ingest\\V1\\Rest\\Opportunity\\Validator' => [
            0  => [
                'required'   => true,
                'validators' => [
                    0 => [
                        'name'    => 'Zend\\Validator\\StringLength',
                        'options' => [
                            'min' => '13',
                            'max' => '13',
                        ],
                    ],
                ],
                'filters'    => [],
                'name'       => 'id',
            ],
            1  => [
                'required'   => true,
                'validators' => [
                    0 => [
                        'name'    => 'Zend\\Validator\\StringLength',
                        'options' => [
                            'min' => '3',
                            'max' => '30',
                        ],
                    ],
                ],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'name',
            ],
            2  => [
                'required'   => true,
                'validators' => [
                    0 => [
                        'name'    => 'Zend\\Validator\\InArray',
                        'options' => [
                            'strict'   => 1,
                            'haystack' => [
                                0 => 'Request',
                                1 => 'Offering',
                            ],
                        ],
                    ],
                ],
                'filters'    => [],
                'name'       => 'type',
            ],
            3  => [
                'required'   => true,
                'validators' => [
                    0 => [
                        'name'    => 'Zend\\Validator\\InArray',
                        'options' => [
                            'strict'   => 1,
                            'haystack' => [
                                0 => 'Technology',
                                1 => 'Commercial',
                                2 => 'Research',
                            ],
                        ],
                    ],
                ],
                'filters'    => [],
                'name'       => 'opportunity_type',
            ],
            4  => [
                'required'   => true,
                'validators' => [
                    0 => [
                        'name'    => 'Zend\\Validator\\StringLength',
                        'options' => [
                            'min' => '2',
                            'max' => '5',
                        ],
                    ],
                ],
                'filters'    => [],
                'name'       => 'country',
            ],
            5  => [
                'required'   => true,
                'validators' => [],
                'filters'    => [],
                'name'       => 'date',
            ],
            6  => [
                'required'   => true,
                'validators' => [],
                'filters'    => [],
                'name'       => 'types',
            ],
            7  => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'description',
            ],
            8  => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'expertise',
            ],
            9  => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'advantage',
            ],
            10 => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'stage',
            ],
            11 => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'stage_reference',
            ],
            12 => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'ipr',
            ],
            13 => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'ipr_reference',
            ],
        ],
        'Ingest\\V1\\Rest\\Event\\Validator'       => [
            0  => [
                'required'   => true,
                'validators' => [
                    0 => [
                        'name'    => 'Zend\\I18n\\Validator\\IsInt',
                        'options' => [],
                    ],
                ],
                'filters'    => [],
                'name'       => 'id',
            ],
            1  => [
                'required'   => true,
                'validators' => [
                    0 => [
                        'name'    => 'Zend\\Validator\\StringLength',
                        'options' => [
                            'min' => '3',
                            'max' => '30',
                        ],
                    ],
                ],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'name',
            ],
            2  => [
                'required'   => true,
                'validators' => [
                    0 => [
                        'name'    => 'Zend\\Validator\\InArray',
                        'options' => [
                            'strict'   => 1,
                            'haystack' => [
                                0 => 'Seminar',
                                1 => 'Brokerage Event',
                                2 => 'Match-making Event',
                                3 => 'Conference',
                            ],
                        ],
                    ],
                ],
                'filters'    => [],
                'name'       => 'type',
            ],
            3  => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'place',
            ],
            4  => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'address',
            ],
            5  => [
                'required'   => true,
                'validators' => [],
                'filters'    => [],
                'name'       => 'date_from',
            ],
            6  => [
                'required'   => true,
                'validators' => [],
                'filters'    => [],
                'name'       => 'date_to',
            ],
            7  => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'description',
            ],
            8  => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'attendee',
            ],
            9  => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'agenda',
            ],
            10 => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'cost',
            ],
            11 => [
                'required'   => true,
                'validators' => [],
                'filters'    => [
                    0 => [
                        'name'    => 'Zend\\Filter\\StringTrim',
                        'options' => [],
                    ],
                ],
                'name'       => 'topics',
            ],
            12 => [
                'required'   => true,
                'validators' => [
                    0 => [
                        'name'    => 'Zend\\I18n\\Validator\\IsFloat',
                        'options' => [],
                    ],
                ],
                'filters'    => [],
                'name'       => 'latitude',
            ],
            13 => [
                'required'   => true,
                'validators' => [
                    0 => [
                        'name'    => 'Zend\\I18n\\Validator\\IsFloat',
                        'options' => [],
                    ],
                ],
                'filters'    => [],
                'name'       => 'longitude',
            ],
        ],
    ],
];
