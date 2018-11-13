<?php

return array_replace_recursive([
    'config'   => [
        'modules'   => [],
        'view'      => [
            'theme_path' => '%app.relative_theme_path%',
            'cache_path' => '%app.relative_theme_cache_path%'
        ],
        'database'  => [
            'host' => '%database.host%',
            'shem' => '%database.shem%',
            'user' => '%database.user%',
            'pass' => '%database.pass%'
        ],
        'app'       => [
            'path'       => '%app.path%',
            'cache_path' => '%app.relative_cache_path%'
        ],
        'debug'     => true,
        'plugin'    => [
            'path' => '%app.relative_plugin_path%'
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
        ]
    ],
    'settings' => [
        'displayErrorDetails' => true
    ]
], ($f = __DIR__ . '/config.user.php') && is_file($f) ? require_once $f : []);