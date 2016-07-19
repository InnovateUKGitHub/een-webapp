<?php

namespace SearchTest;

use Search\Module;
use Search\V1\ElasticSearch\Factory\ElasticSearchServiceFactory;
use Search\V1\ElasticSearch\Factory\QueryServiceFactory;
use Search\V1\ElasticSearch\Service\ElasticSearchService;
use Search\V1\ElasticSearch\Service\QueryService;

/**
 * @covers Search\Module
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigIsCorrect()
    {
        $module = new Module();

        self::assertEquals([
            'service_manager'        => [
                'factories' => [
                    ElasticSearchService::class => ElasticSearchServiceFactory::class,
                    QueryService::class => QueryServiceFactory::class,
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
                'Search\\V1\\Rpc\\Event\\Controller' => [
                    'input_filter' => 'Search\\V1\\Rpc\\Event\\Validator',
                ],
            ],
            'input_filter_specs'     => [
                'Search\\V1\\Rpc\\Opportunity\\Validator'  => [
                    0 => [
                        'required'   => true,
                        'validators' => [],
                        'filters'    => [],
                        'name'       => 'from',
                    ],
                    1 => [
                        'required'   => true,
                        'validators' => [],
                        'filters'    => [],
                        'name'       => 'size',
                    ],
                    2 => [
                        'required'   => true,
                        'validators' => [],
                        'filters'    => [],
                        'name'       => 'search',
                    ],
                    3 => [
                        'required'   => true,
                        'validators' => [],
                        'filters'    => [],
                        'name'       => 'sort',
                    ],
                    4 => [
                        'required'   => true,
                        'validators' => [],
                        'filters'    => [],
                        'name'       => 'source',
                    ],
                ],
                'Search\\V1\\Rpc\\Event\\Validator'  => [
                    0 => [
                        'required'   => true,
                        'validators' => [],
                        'filters'    => [],
                        'name'       => 'from',
                    ],
                    1 => [
                        'required'   => true,
                        'validators' => [],
                        'filters'    => [],
                        'name'       => 'size',
                    ],
                    2 => [
                        'required'   => true,
                        'validators' => [],
                        'filters'    => [],
                        'name'       => 'search',
                    ],
                    3 => [
                        'required'   => true,
                        'validators' => [],
                        'filters'    => [],
                        'name'       => 'sort',
                    ],
                    4 => [
                        'required'   => true,
                        'validators' => [],
                        'filters'    => [],
                        'name'       => 'source',
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
                        0 => 'POST',
                    ],
                    'route_name'   => 'een.rpc.opportunity',
                ],
                'Search\\V1\\Rpc\\Event\\Controller'       => [
                    'service_name' => 'Event',
                    'http_methods' => [
                        0 => 'POST',
                    ],
                    'route_name'   => 'een.rpc.event',
                ],
            ],
        ], $module->getConfig());
    }

    public function testAutoloaderConfig()
    {
        $module = new Module();

        $result = $module->getAutoloaderConfig();

        self::assertArrayHasKey('ZF\Apigility\Autoloader', $result);
        self::assertArrayHasKey('namespaces', $result['ZF\Apigility\Autoloader']);
        self::assertArrayHasKey('Search', $result['ZF\Apigility\Autoloader']['namespaces']);
    }
}
