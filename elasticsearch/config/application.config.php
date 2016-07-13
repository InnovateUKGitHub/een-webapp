<?php

return [
    'modules'                 => include __DIR__ . '/modules.config.php',
    'module_listener_options' => [
        'module_paths'             => [
            './module',
            './vendor',
        ],
        'config_glob_paths'        => [realpath(__DIR__) . '/autoload/{,*.}{global,local}.php'],
        'config_cache_key'         => 'application.config.cache',
        'config_cache_enabled'     => true,
        'module_map_cache_key'     => 'application.module.cache',
        'module_map_cache_enabled' => true,
        'cache_dir'                => 'data/cache/',
    ],
];
