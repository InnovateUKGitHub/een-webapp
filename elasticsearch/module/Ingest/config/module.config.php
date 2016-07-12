<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'Ingest\\V1\\Rest\\Opportunity\\OpportunityResource' => 'Ingest\\V1\\Rest\\Opportunity\\OpportunityResourceFactory',
            'Ingest\\V1\\Rest\\Event\\EventResource' => 'Ingest\\V1\\Rest\\Event\\EventResourceFactory',
        ),
    ),
    'router' => array(
        'routes' => array(
            'ingest.rest.opportunity' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/opportunity[/:opportunity_id]',
                    'defaults' => array(
                        'controller' => 'Ingest\\V1\\Rest\\Opportunity\\Controller',
                    ),
                ),
            ),
            'ingest.rest.event' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/event[/:event_id]',
                    'defaults' => array(
                        'controller' => 'Ingest\\V1\\Rest\\Event\\Controller',
                    ),
                ),
            ),
        ),
    ),
    'zf-versioning' => array(
        'uri' => array(
            0 => 'ingest.rest.opportunity',
            1 => 'ingest.rest.event',
        ),
    ),
    'zf-rest' => array(
        'Ingest\\V1\\Rest\\Opportunity\\Controller' => array(
            'listener' => 'Ingest\\V1\\Rest\\Opportunity\\OpportunityResource',
            'route_name' => 'ingest.rest.opportunity',
            'route_identifier_name' => 'opportunity_id',
            'collection_name' => 'opportunity',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'Ingest\\V1\\Rest\\Opportunity\\OpportunityEntity',
            'collection_class' => 'Ingest\\V1\\Rest\\Opportunity\\OpportunityCollection',
            'service_name' => 'Opportunity',
        ),
        'Ingest\\V1\\Rest\\Event\\Controller' => array(
            'listener' => 'Ingest\\V1\\Rest\\Event\\EventResource',
            'route_name' => 'ingest.rest.event',
            'route_identifier_name' => 'event_id',
            'collection_name' => 'event',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'Ingest\\V1\\Rest\\Event\\EventEntity',
            'collection_class' => 'Ingest\\V1\\Rest\\Event\\EventCollection',
            'service_name' => 'Event',
        ),
    ),
    'zf-content-negotiation' => array(
        'controllers' => array(
            'Ingest\\V1\\Rest\\Opportunity\\Controller' => 'HalJson',
            'Ingest\\V1\\Rest\\Event\\Controller' => 'HalJson',
        ),
        'accept_whitelist' => array(
            'Ingest\\V1\\Rest\\Opportunity\\Controller' => array(
                0 => 'application/vnd.ingest.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'Ingest\\V1\\Rest\\Event\\Controller' => array(
                0 => 'application/vnd.ingest.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
        ),
        'content_type_whitelist' => array(
            'Ingest\\V1\\Rest\\Opportunity\\Controller' => array(
                0 => 'application/vnd.ingest.v1+json',
                1 => 'application/json',
            ),
            'Ingest\\V1\\Rest\\Event\\Controller' => array(
                0 => 'application/vnd.ingest.v1+json',
                1 => 'application/json',
            ),
        ),
    ),
    'zf-hal' => array(
        'metadata_map' => array(
            'Ingest\\V1\\Rest\\Opportunity\\OpportunityEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'ingest.rest.opportunity',
                'route_identifier_name' => 'opportunity_id',
                'hydrator' => 'Zend\\Hydrator\\ArraySerializable',
            ),
            'Ingest\\V1\\Rest\\Opportunity\\OpportunityCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'ingest.rest.opportunity',
                'route_identifier_name' => 'opportunity_id',
                'is_collection' => true,
            ),
            'Ingest\\V1\\Rest\\Event\\EventEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'ingest.rest.event',
                'route_identifier_name' => 'event_id',
                'hydrator' => 'Zend\\Hydrator\\ArraySerializable',
            ),
            'Ingest\\V1\\Rest\\Event\\EventCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'ingest.rest.event',
                'route_identifier_name' => 'event_id',
                'is_collection' => true,
            ),
        ),
    ),
    'zf-content-validation' => array(
        'Ingest\\V1\\Rest\\Opportunity\\Controller' => array(
            'input_filter' => 'Ingest\\V1\\Rest\\Opportunity\\Validator',
        ),
    ),
    'input_filter_specs' => array(
        'Ingest\\V1\\Rest\\Opportunity\\Validator' => array(
            0 => array(
                'required' => true,
                'validators' => array(),
                'filters' => array(),
                'name' => 'id',
            ),
            1 => array(
                'required' => true,
                'validators' => array(),
                'filters' => array(),
                'name' => 'name',
            ),
        ),
    ),
);
