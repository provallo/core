<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Components\Database\MigrationRunner;
use ProVallo\Components\Plugin\Instance;
use ProVallo\Core;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbMigrateCommand extends Command
{
    
    protected $force = false;
    
    public function configure ()
    {
        $this->setName('db:migrate');
        $this->setDescription('Run db migrations');
        
        $this->addOption('plugin', null, InputOption::VALUE_REQUIRED, 'Execute migrations of a specific plugin');
    }
    
    public function execute (InputInterface $input, OutputInterface $output)
    {
        $plugin = $input->getOption('plugin');
        
        if (empty($plugin))
        {
            $directory = realpath(__DIR__ . '/../../update-assets/migrations/');
            $runner    = new MigrationRunner(-1, $directory);
            $runner->setOutput($output);
        }
        else
        {
            $plugin = Core::plugins()->loadInstance($plugin);
            
            if ($plugin instanceof Instance)
            {
                $directory = path($plugin->getPath(), 'Migrations');
                $runner    = new MigrationRunner($plugin->getModel()->id, $directory);
                $runner->setOutput($output);
                $runner->setNamespace('ProVallo\\Plugins\\' . $plugin->getName() . '\\Migrations\\');
            }
            else
            {
                $output->writeln('Plugin not found');
                return;
            }
        }
        
        $result = $runner->run();
        
        if (isSuccess($result))
        {
            $output->writeln($result['total'] . ' migrations were executed');
        }
        else
        {
            throw $result['exception'];
        }
    }
    
}