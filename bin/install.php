<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$plugin  = json_decode(file_get_contents(__DIR__ . '/../plugin.json'), true);
$loader  = require_once __DIR__ . '/../vendor/autoload.php';
$app     = new \ProVallo\Core(null, $loader, \ProVallo\Core::API_CONSOLE);
$console = new \Symfony\Component\Console\Application('ProVallo Console Commands', $plugin['version']);

$console->addCommands([
    new \ProVallo\Commands\DbInstallCommand()
]);

$console->run(new \Symfony\Component\Console\Input\ArrayInput(['db:install']));