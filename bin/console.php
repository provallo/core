<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$loader  = require_once __DIR__ . '/../vendor/autoload.php';
$app     = new \ProVallo\Core(null, $loader);
$console = new \Symfony\Component\Console\Application('ProVallo Console Commands', '1.0.0');

$console->addCommands([
    new \ProVallo\Commands\PluginListCommand(),
    new \ProVallo\Commands\PluginInstallCommand(),
    new \ProVallo\Commands\PluginUninstallCommand(),
    new \ProVallo\Commands\PluginUpdateListCommand(),
    
    new \ProVallo\Commands\DbInstallCommand(),
    new \ProVallo\Commands\DbMigrateCommand()
]);

$app->executePlugins($app->plugins());

if ($commands = $app->events()->collect('core.console_commands.collect'))
{
    $console->addCommands($commands);
}

$console->run();