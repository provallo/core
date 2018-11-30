<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$loader  = require_once __DIR__ . '/../vendor/autoload.php';
$app     = new \ProVallo\Core(null, $loader);
$console = new \Symfony\Component\Console\Application('ProVallo Console Commands', '1.0.0');

$console->addCommands([
    new \ProVallo\Commands\DbInstallCommand()
]);

$console->run(new \Symfony\Component\Console\Input\ArrayInput(['db:install']));