<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$loader = require_once __DIR__ . '/vendor/autoload.php';
$app    = new \ProVallo\Core(null, $loader, \ProVallo\Core::API_WEB);

$app->run();