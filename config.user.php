<?php

return [
    'config'   => [
        'view'      => [
            'theme_path' => 'themes/',
            'cache_path' => 'cache/twig/'
        ],
        'database'  => [
            'host' => 'rm01',
            'shem' => 'pv',
            'user' => 'rm-dev',
            'pass' => 'rm-dev-2010'
        ],
        'app'       => [
            'path'       => __DIR__ . '/',
            'cache_path' => 'cache/'
        ],
        'plugin'    => [
            'path' => '%app.relative_plugin_path%'
        ]
    ]
];