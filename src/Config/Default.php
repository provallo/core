<?php

return [
    'config'   => [
        'modules'   => [],
        'view'      => [
            'theme_path' => 'themes/',
            'cache_path' => 'cache/twig/'
        ],
        'database'  => [
            'host' => '%database.host%',
            'shem' => '%database.shem%',
            'user' => '%database.user%',
            'pass' => '%database.pass%'
        ],
        'app'       => [
            'path'       => realpath(__DIR__ . '/../..'),
            'cache_path' => 'cache/'
        ],
        'plugin'    => [
            'path' => 'ext/'
        ],
        'httpCache' => [
            'enabled' => true
        ],
        'cookie'    => [
            'enabled' => true,
            'config'  => [
                'name'        => 'provallo',
                'lifetime'    => '1 year',
                'autorefresh' => true
            ]
        ],
        'debug' => true
    ],
    'settings' => [
        'displayErrorDetails' => true
    ]
];