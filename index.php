<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/src/Core.php';

$app = new \ProVallo\Core();

$app->setPaths([
    'app_path'         => __DIR__ . '/',
    'app_cache_path'   => 'cache/',
    'theme_path'       => 'themes/',
    'theme_cache_path' => 'cache/twig/',
    'plugin_path'      => 'ext/'
]);

$app->run();