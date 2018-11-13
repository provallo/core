<?php

use Favez\Mvc\App;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

$app     = new \ProVallo\Core();
$console = new \Symfony\Component\Console\Application('ProVallo Console Commands', '1.0.0');

$console->addCommands([
    new \ProVallo\Commands\PluginListCommand(),
    new \ProVallo\Commands\PluginInstallCommand(),
    new \ProVallo\Commands\PluginUninstallCommand(),
    
    new \ProVallo\Commands\DbInstallCommand(),
    new \ProVallo\Commands\DbMigrateCommand()
]);

if ($commands = App::events()->collect('core.console_commands.collect'))
{
    $console->addCommands($commands);
}

$console->run();